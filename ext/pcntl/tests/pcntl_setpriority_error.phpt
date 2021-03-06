--TEST--
pcntl_setpriority() - Wrong process identifier
--EXTENSIONS--
pcntl
--SKIPIF--
<?php

if (!function_exists('pcntl_setpriority')) {
    die('skip pcntl_setpriority doesn\'t exist');
}
?>
--FILE--
<?php

try {
    pcntl_setpriority(0, null, 42);
} catch (ValueError $exception) {
    echo $exception->getMessage() . "\n";
}

?>
--EXPECT--
pcntl_setpriority(): Argument #3 ($mode) must be one of PRIO_PGRP, PRIO_USER, or PRIO_PROCESS
