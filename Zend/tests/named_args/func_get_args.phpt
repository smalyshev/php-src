--TEST--
func_get_args() skips arguments that weren't passed
--FILE--
<?php

function test($a, $b = "b", $c = "c", ...$rest) {
    var_dump(func_get_args());
    var_dump(func_get_arg(0), func_get_arg(1), func_get_arg(2), func_get_arg(3));
    var_dump(func_num_args());
}

test(c => "C", a => "A");
test(c => "C", a => "A", d => "D");
test(c => "C", b => "B");
test(c => "C");

function test2($a = FOO, $b = [FOO, BAR], $c = null) {
    var_dump(func_get_args());
    var_dump(func_get_arg(0), func_get_arg(1), func_get_arg(2));
}

define('FOO', 'FOO value');
define('BAR', 'BAR value');

test2(c => 42);

?>
--EXPECTF--
array(3) {
  [0]=>
  string(1) "A"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "C"
}

Warning: func_get_arg():  Argument 3 not passed to function in %s on line %d
string(1) "A"
string(1) "b"
string(1) "C"
bool(false)
int(3)
array(3) {
  [0]=>
  string(1) "A"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "C"
}

Warning: func_get_arg():  Argument 3 not passed to function in %s on line %d
string(1) "A"
string(1) "b"
string(1) "C"
bool(false)
int(3)

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d
array(3) {
  [0]=>
  NULL
  [1]=>
  string(1) "B"
  [2]=>
  string(1) "C"
}

Warning: func_get_arg():  Argument 3 not passed to function in %s on line %d
NULL
string(1) "B"
string(1) "C"
bool(false)
int(3)

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d
array(3) {
  [0]=>
  NULL
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "C"
}

Warning: func_get_arg():  Argument 3 not passed to function in %s on line %d
NULL
string(1) "b"
string(1) "C"
bool(false)
int(3)
array(3) {
  [0]=>
  string(9) "FOO value"
  [1]=>
  array(2) {
    [0]=>
    string(9) "FOO value"
    [1]=>
    string(9) "BAR value"
  }
  [2]=>
  int(42)
}
string(9) "FOO value"
array(2) {
  [0]=>
  string(9) "FOO value"
  [1]=>
  string(9) "BAR value"
}
int(42)
