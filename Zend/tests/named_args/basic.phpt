--TEST--
Basic named arguments
--FILE--
<?php

function test($a, $b, $c = "c", $d = "d", $e = "e") {
    var_dump($a, $b, $c, $d, $e);
}

test(a => "A", b => "B");
test(a => "A", b => "B", c => "C", d => "D", e => "E");
test(d => "D", a => "A", c => "C", e => "E", b => "B");
test(d => "D", b => "B", a => "A");
test(d => "D", b => "B");
test("A", "B", d => "D");
test("A", e => "E", b => "B");

?>
--EXPECTF--
string(1) "A"
string(1) "B"
string(1) "c"
string(1) "d"
string(1) "e"
string(1) "A"
string(1) "B"
string(1) "C"
string(1) "D"
string(1) "E"
string(1) "A"
string(1) "B"
string(1) "C"
string(1) "D"
string(1) "E"
string(1) "A"
string(1) "B"
string(1) "c"
string(1) "D"
string(1) "e"

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d
NULL
string(1) "B"
string(1) "c"
string(1) "D"
string(1) "e"
string(1) "A"
string(1) "B"
string(1) "c"
string(1) "D"
string(1) "e"
string(1) "A"
string(1) "B"
string(1) "c"
string(1) "d"
string(1) "E"
