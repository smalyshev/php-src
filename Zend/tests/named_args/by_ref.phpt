--TEST--
Named arguments respect by-reference passing semantics
--FILE--
<?php

function test($a, &$b, $c = 0, &$d = 0) {
    var_dump($a, $b, $c, $d);
    $b++; $d++;
}

test(a => $a1, b => $b1, c => $c1, d => $d1);
var_dump($b1, $d1);

test(c => $c2, b => $b2, d => $d2, a => $a1);
var_dump($b2, $d2);

test(b => $b3, d => $d3);
var_dump($b3, $d3);

$arr1 = [];
test(c => $arr1['c'], b => $arr1['b'], d => $arr1['d'], a => $arr1['a']);
var_dump($arr1);

$test = 'test';
$arr2 = [];
test(c => $arr2['c'], b => $arr2['b'], d => $arr2['d'], a => $arr2['a']);
var_dump($arr2);

?>
--EXPECTF--
Notice: Undefined variable: a1 in %s on line %d

Notice: Undefined variable: c1 in %s on line %d
NULL
NULL
NULL
NULL
int(1)
int(1)

Notice: Undefined variable: c2 in %s on line %d

Notice: Undefined variable: a1 in %s on line %d
NULL
NULL
NULL
NULL
int(1)
int(1)

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d
NULL
NULL
int(0)
NULL
int(1)
int(1)

Notice: Undefined index: c in %s on line %d

Notice: Undefined index: a in %s on line %d
NULL
NULL
NULL
NULL
array(2) {
  ["b"]=>
  int(1)
  ["d"]=>
  int(1)
}

Notice: Undefined index: c in %s on line %d

Notice: Undefined index: a in %s on line %d
NULL
NULL
NULL
NULL
array(2) {
  ["b"]=>
  int(1)
  ["d"]=>
  int(1)
}
