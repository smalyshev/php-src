# Module to enable printing PHP data structures with LLDB
# Up-to-date for version: PHP 5.5
# TODO: no ZTS support yet
import lldb

# Names of ZVAL types
ZVAL_TYPES = [
    "NULL",
    "long",
    "double",
    "bool",
    "array",
    "object",
    "string",
    "resource",
    "constant",
    "const_array"
]

def equal_pointers(ptr1, ptr2):
    """
    Checks if two pointer values are equal. For now trivial, unless we discover it's not enough.
    Args:
        ptr1: SBValue
        ptr2: SBValue
    """
    return ptr1.GetValueAsUnsigned() == ptr2.GetValueAsUnsigned()

class ZvalPrinter(object):
    """
    This is the class for printing zvals
    Properties:
        debugger: debugger instance
        frame: SBFrame instance
        zval_type: representation of zval
        zval_type_p: representation of zval *
        zval_type_pp: representation of zval **
        err: SBError object for use in conversion functions
        indentsize: indent depth ofr printing
        result: debugger result object
    """
    def __init__(self, debugger):
        self.debugger = debugger
        target = debugger.GetSelectedTarget()
        process = target.GetProcess()
        thread = process.GetSelectedThread()
        self.frame = thread.GetSelectedFrame()
        self.zval_type = target.FindFirstType('zval')
        self.zval_type_p = self.zval_type.GetPointerType()
        self.zval_type_pp = self.zval_type_p.GetPointerType()
        self.err = lldb.SBError()
        self.indentsize = 0
        self.eg = self.frame.EvaluateExpression("executor_globals")
    
    def printer(self, value, eol=False):
        """
        Print value into result.
        Args:
            value: printable value
            eol: should we add EOL at the end
        """
        if eol:
            print >>self.result, value
        else:
            print >>self.result, value,
            
    def indent(self):
        """
        Indent current line
        """
        if self.indentsize > 0:
            self.printer(" "*self.indentsize)
            
    def eol(self):
        """
        End the line
        """
        print >>self.result, ""
    
    def start(self, result):
        """
        Start printing process into the result
        """
        self.indentsize = 0
        self.result = result
        
    def void_to_type(self, pData, ztype):
        """
        Convert void* to specific pointer type
        Args:
            pData: void * SBValue
            ztype: target type as string
        Returns: SBValue of needed type pointing to the same place as pData
        """
        # FIXME: maybe there's a better way to do it? Looks hackish.
        return self.frame.EvaluateExpression("(%s)0x%x" % (ztype, pData.GetValueAsUnsigned()))
    
    def printzv(self, arg):
        """
        Print zval *
        """
        argtype = arg.GetTypeName()
        if argtype != 'zval *':
            self.printer("Invalid expression - must be zval *", True)
            return
        # TODO: support other zval types like zval, zval **, etc.
        unzval = self.eg.GetChildMemberWithName("uninitialized_zval_ptr")
        if equal_pointers(unzval, arg):
            self.printer("*uninitialized*", True)
            return
        self.printzv_contents(arg)
        
    def printzv_contents(self, zval):
        """
        Print the contents of zval
        """
        self.printer("[0x%x]" % zval.GetValueAsUnsigned())
        ztype = zval.GetChildMemberWithName("type").GetValueAsUnsigned()
        if ztype >= len(ZVAL_TYPES):
            self.printer("[Unknown type: %d]" % ztype)
            return
        self.printer("[%d@%s]" % (ztype, ZVAL_TYPES[ztype]))
    
        res = None
        # Per-type clauses:
        if ztype == 1 or ztype == 7:
            # int and resource
            self.printer(zval.GetValueForExpressionPath(".value.lval").GetValueAsSigned(), True)
        elif ztype == 0:
            # null
            self.eol()
        elif ztype == 2:
            # double
            self.printer(zval.GetValueForExpressionPath(".value.dval").GetData().GetDouble(self.err, 0), True)
        elif ztype == 3:
            # bool
            if zval.GetValueForExpressionPath(".value.lval").GetValueAsSigned() == 0:
                res = "FALSE"
            else:
                res = "TRUE"
            self.printer(res, True)
        elif ztype == 4:
            # array
            self.print_ht_wrapped(zval.GetValueForExpressionPath(".value.ht"), True)
        elif ztype == 5:
            # object
            handle = zval.GetValueForExpressionPath(".value.obj.handle")
            handlers = zval.GetValueForExpressionPath(".value.obj.handlers")
            zobj = self.frame.EvaluateExpression("zend_objects_get_address((zval *)0x%x)" % zval.GetValueAsUnsigned())
            if equal_pointers(handlers.GetChildMemberWithName("get_class_entry"), self.frame.EvaluateExpression("&zend_std_object_get_class")):
                cname = zobj.GetValueForExpressionPath(".ce.name").GetSummary().strip('"')
                ce = zobj.GetChildMemberWithName("ce")
            else:
                cname = "Unknown" 
                ce = None
            # classname#OBJECTID
            self.printer("%s#%d" % (cname, handle.GetValueAsUnsigned()))
            if equal_pointers(handlers.GetChildMemberWithName("get_properties"), self.frame.EvaluateExpression("&zend_std_get_properties")):
                ht = zobj.GetChildMemberWithName("properties")
                if ht.GetValueAsUnsigned() != 0:
                    # has regular properties
                    self.print_ht_wrapped(ht)
                elif ce:
                    # fixed properties table
                    propinfo = ce.GetChildMemberWithName("properties_info")
                    self.apply_ht(propinfo, self.print_prop, zobj.GetChildMemberWithName("properties_table"))
                    self.eol
                else:
                    # nothing 
                    self.eol()
            else:
                # TODO: try to call get_properties and print?
                self.eol()
        elif ztype == 6:
            # string
            self.print_str(zval.GetValueForExpressionPath(".value.str.val"), zval.GetValueForExpressionPath(".value.str.len"))
            self.eol()
        
    def print_prop(self, key, keylen, h, pData, proptable):
        """
        Print out properties table for an object as packed table
        Args:
            key: property name
            keylen: property length
            h: number (should not actuall be used)
            pData: void * top property info
            proptable: SBValue containing object's property array
        """
        self.indent()
        pinfo = self.void_to_type(pData, "zend_property_info *")
        offset = pinfo.GetChildMemberWithName("offset").GetValueAsUnsigned()
        propval = proptable.GetChildAtIndex(offset)
        if key != None:
            self.print_str(key, keylen)
        else:
            self.printer("%d" % h)
        self.printer("=>")
        self.printzv_contents(propval)
    
    def print_str(self, strval, slen):
        """
        Print a string. Characters < 0x20 are encoded.
        Args:
            strval: string value as SBValue
            slen: string length as number or SBValue
        """
        if isinstance(slen, lldb.SBValue):
            slen = slen.GetValueAsUnsigned()
        data = strval.GetPointeeData(0, slen)
        res = ''
        for i in xrange(0, slen):
            ch = data.GetUnsignedInt8(self.err, i)
            if ch == 0:
                res += '\\0';
            elif ch < 0x20:
                res += '\\'+hex(ord(data[i]))[1:]
            else:
                res += chr(ch)
        self.printer("[%d:%s]" % (slen, res))
    
    def print_ht_wrapped(self, ht, recurse=False):
        """
        Print hash table wrapped in {} and indented
        """
        self.printer("(%d) {" % ht.GetChildMemberWithName("nNumOfElements").GetValueAsUnsigned(), True)
        self.indentsize += 1
        self.print_ht(ht, recurse)
        self.indentsize -= 1
        self.indent()
        self.printer("}", True)
    
    def apply_ht(self, ht, callback, context=None):
        """
        Apply callback to hashtable contents.
        For each hashtable entry, callback is called with:
            callback(key, keylen, numericKey, pData, context)
        where pData is SBValue with void* from the hash table. 
        Args:
            ht: SBValue with hash table
            callback: called function
            context: any context, passed to the callback
        """
        self.printer("(%d) {" % ht.GetChildMemberWithName("nNumOfElements").GetValueAsUnsigned(), True)
        self.indentsize += 1
        p = ht.GetChildMemberWithName("pListHead")
        while p.GetValueAsUnsigned() != 0:
            keylen = p.GetChildMemberWithName("nKeyLength").GetValueAsUnsigned()
            if keylen > 0:
                key = p.GetChildMemberWithName("arKey")
                keylen -= 1
                h = None
            else:
                h = p.GetChildMemberWithName("h").GetValueAsUnsigned()
                key = None
            callback(key, keylen, h, p.GetChildMemberWithName("pData"), context)
            p = p.GetChildMemberWithName("pListNext")
        self.indentsize -= 1
        self.indent()
        self.printer("}", True)
    
    def print_ht(self, ht, recurse=False):
        """
        Print hash table contents.
        Args:
            ht: SBValue with hash table
            recurse: should we print zvals inside the table or only display pointers?
        """
        p = ht.GetChildMemberWithName("pListHead")
        while p.GetValueAsUnsigned() != 0:
            self.indent()
            keylen = p.GetChildMemberWithName("nKeyLength").GetValueAsUnsigned()
            if keylen > 0:
                self.print_str(p.GetChildMemberWithName("arKey"), keylen-1)
            else:
                self.printer("%d" % (p.GetChildMemberWithName("h").GetValueAsUnsigned()))
            self.printer("=>")
            if recurse:
                pdata = p.GetChildMemberWithName("pData")
                pzdata = pdata.Cast(self.zval_type_pp)
                self.printzv_contents(pzdata.Dereference())
            else:
                self.printer("0x%x" % p.GetChildMemberWithName("pData").GetValueAsUnsigned(), True)
            p = p.GetChildMemberWithName("pListNext")

def printzv(debugger, command, result, internal_dict):
    """
    LLDB callback function for printing zval
    """
    zp = ZvalPrinter(debugger)
    zp.start(result)
    zp.printzv(zp.frame.EvaluateExpression(command))
    
def phphelp(debugger, command, result, internal_dict):
    """
    A help text for PHP commands.
    """
    print >>result, "printzv <expression> - print zval, expression should evaluate to zval *"
    
def __lldb_init_module(debugger, internal_dict):
    debugger.HandleCommand('command script add -f phpdb.printzv printzv')
    debugger.HandleCommand('command script add -f phpdb.phphelp phphelp')
    print 'The PHP commands have been installed. Use \'phphelp\' if you need help.'
