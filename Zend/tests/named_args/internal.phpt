--TEST--
Named arguments work with internal functions
--FILE--
<?php

// array_fill(int $start_key, int $num, mixed $val)

var_dump(array_fill(start_key => 0, num => 3, val => 42));
var_dump(array_fill(val => 42, start_key => 1, num => 2));
var_dump(array_fill(2, val => 42, num => 1));

// array_slice(array $input, int $offset, int $length = NULL, bool $preserve_keys = false)

var_dump(array_slice(arg => [1, 2, 3, 4, 5], offset => 2, length => 2));
var_dump(array_slice(length => 2, offset => 2, arg => [1, 2, 3, 4, 5]));
var_dump(array_slice(arg => ['a' => 0, 'b' => 1], offset => 1, preserve_keys => true));
var_dump(array_slice(['a' => 0, 'b' => 1], preserve_keys => true, offset => 1));

// missing required arg:
var_dump(array_slice(offset => 2));

// by_ref:
// array_splice(array &$input, int $offset, int $length = 0, mixed $replacement = [])

$array = [1, 2, 3, 4, 5];
var_dump(array_splice(replacement => ["3", "4"], offset => 2, arg => $array));
var_dump($array);

$array = [1, 2, 3, 4, 5];
var_dump(array_splice($array, 2, replacement => ["3", "4"]));
var_dump($array);

?>
--EXPECTF--
array(3) {
  [0]=>
  int(42)
  [1]=>
  int(42)
  [2]=>
  int(42)
}
array(2) {
  [1]=>
  int(42)
  [2]=>
  int(42)
}
array(1) {
  [2]=>
  int(42)
}
array(2) {
  [0]=>
  int(3)
  [1]=>
  int(4)
}
array(2) {
  [0]=>
  int(3)
  [1]=>
  int(4)
}
array(1) {
  ["b"]=>
  int(1)
}
array(1) {
  ["b"]=>
  int(1)
}

Warning: Parameter 1 missing for array_slice() in %s on line %d
NULL
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(4)
  [2]=>
  int(5)
}
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
}
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(4)
  [2]=>
  int(5)
}
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
}
