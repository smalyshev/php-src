--TEST--
Collecting unknown named arguments in a variadic function
--FILE--
<?php

function test($a, ...$opts) {
    var_dump($a, $opts);
}

test(a => "a", b => "b", c => "c");
test(c => "c", b => "b", a => "a");
test(c => "c", b => "b");
test("a", b => "b", b => "B");
test("a", "b", "c", e => "e", d => "d");

function test2(&...$opts) {
    foreach ($opts as $name => &$opt) {
        $opt = $name;
    }
}

test2(b => $b, a => $a);
var_dump($a, $b);

?>
--EXPECTF--
string(1) "a"
array(2) {
  ["b"]=>
  string(1) "b"
  ["c"]=>
  string(1) "c"
}
string(1) "a"
array(2) {
  ["c"]=>
  string(1) "c"
  ["b"]=>
  string(1) "b"
}

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d
NULL
array(2) {
  ["c"]=>
  string(1) "c"
  ["b"]=>
  string(1) "b"
}

Warning: Overwriting already passed parameter $b in %s on line %d
string(1) "a"
array(1) {
  ["b"]=>
  string(1) "B"
}
string(1) "a"
array(4) {
  [0]=>
  string(1) "b"
  [1]=>
  string(1) "c"
  ["e"]=>
  string(1) "e"
  ["d"]=>
  string(1) "d"
}
string(1) "a"
string(1) "b"
