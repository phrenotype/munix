<?php

use Munix\Munix;
use Munix\MunixId;

require "Munix/MunixId.php";
require "Munix/Munix.php";

$a = [];

/*
foreach (range(0, 10000) as $v) {
    $id = Munix::nextId(5);
    $a[] = $id;
    echo $id, PHP_EOL;
}

echo PHP_EOL;
echo count(array_unique($a));

*/

$obj = Munix::nextIdAsObject(5);

print_r($obj);
