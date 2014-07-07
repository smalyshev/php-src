--TEST--
A warning is raised if you try to overwrite a argument that was already passed
--FILE--
<?php

function test($a, $b) {
    var_dump($a, $b);
}

test(a => 1, a => 2, b => 3);
test(b => 1, a => 2, a => 3);
test(b => 1, b => 2, b => 3);

?>
--EXPECTF--
Warning: Overwriting already passed parameter 1 ($a) in %s on line %d
int(2)
int(3)

Warning: Overwriting already passed parameter 1 ($a) in %s on line %d
int(3)
int(1)

Warning: Overwriting already passed parameter 2 ($b) in %s on line %d

Warning: Overwriting already passed parameter 2 ($b) in %s on line %d

Warning: Missing argument 1 for test(), called in %s on line %d and defined in %s on line %d

Notice: Undefined variable: a in %s on line %d
NULL
int(3)
