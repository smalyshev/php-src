--TEST--
Unknown named arguments to non-variadic functions throw a fatal error
--FILE--
<?php

function test($test) { }

test(tets => 42);

?>
--EXPECTF--
Fatal error: Unknown named argument $tets in %s on line %d
