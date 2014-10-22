--TEST--
testing hashKey returns false
--FILE--
<?php
class Foo {
    public function __hashKey() {
        return false;
    }
}

$foo = new Foo();
$test = [
    $foo => true
];

var_dump($test);
?>
--EXPECT--	
array(1) {
  [0]=>
  bool(true)
}


