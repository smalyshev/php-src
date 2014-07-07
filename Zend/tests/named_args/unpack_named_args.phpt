--TEST--
Unpacking named args
--FILE--
<?php

function test($a, ...$opts) {
    var_dump($a, $opts);
}
function test2($a, $b, $c) {
    var_dump($a, $b, $c);
}

function gen($array) {
    foreach ($array as $k => $v) yield $k => $v;
}
function gen2() {
    yield [1, 2, 3] => "a";
    yield 3.14 => "b";
    yield "c" => "C";
    yield "d" => "D";
}

test(...["a", "b", "c" => "C", "d" => "D"]);
test(...["a", "c" => "C", "d" => "D", "b"]);
test("a", ...["b", "c" => "C", "d" => "D"]);
test(a => "A", ...["b", "c" => "C", "d" => "D"]);
test(...["a", "a" => "A"]);

test(...gen(["a", "b", "c" => "C", "d" => "D"]));
test(...gen(["c" => "C", "d" => "D", "a", "b"]));
test("a", ...gen(["b", "c" => "C", "d" => "D"]));
test(...gen2());

test2("a", "b", ...["c" => "C"]);
test2(b => "B", ...["c" => "C", "a" => "A"]);
test2(c => "C", ...["a", "b"]);

?>
--EXPECTF--
string(1) "a"
array(3) {
  [0]=>
  string(1) "b"
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}

Warning: Cannot pass positional arguments after named arguments. Aborting argument unpacking in %s on line %d
string(1) "a"
array(2) {
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}
string(1) "a"
array(3) {
  [0]=>
  string(1) "b"
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}

Warning: Cannot pass positional arguments after named arguments. Aborting argument unpacking in %s on line %d
string(1) "A"
array(0) {
}

Warning: Overwriting already passed parameter 1 ($a) in %s on line %d
string(1) "A"
array(0) {
}
string(1) "a"
array(3) {
  [0]=>
  string(1) "b"
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}

Warning: Cannot pass positional arguments after named arguments. Aborting argument unpacking in %s on line %d

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d
NULL
array(2) {
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}
string(1) "a"
array(3) {
  [0]=>
  string(1) "b"
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}
string(1) "a"
array(3) {
  [0]=>
  string(1) "b"
  ["c"]=>
  string(1) "C"
  ["d"]=>
  string(1) "D"
}
string(1) "a"
string(1) "b"
string(1) "C"
string(1) "A"
string(1) "B"
string(1) "C"

Warning: Cannot pass positional arguments after named arguments. Aborting argument unpacking in %s on line %d

Warning: Missing argument 1 for test2(), called in %s on line %d and defined in %s on line %d

Warning: Missing argument 2 for test2(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d

Notice: Undefined variable: b in %s on line %d
NULL
NULL
string(1) "C"
