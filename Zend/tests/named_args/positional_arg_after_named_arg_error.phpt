--TEST--
Positional arguments cannot be used after named arguments
--FILE--
<?php

strpos(haystack => "foobar", "bar");

?>
--EXPECTF--
Fatal error: Cannot pass positional arguments after named arguments in %s on line %d
