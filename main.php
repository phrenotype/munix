<?php

use Munix\Munix;
use Munix\MunixId;

require "Munix/MunixId.php";
require "Munix/Munix.php";



$a = [];



$start = time();


$count = 100;

foreach (range(0, $count) as $v) {
    $id = Munix::nextId(5);
    $a[] = $id;
    echo $id, PHP_EOL;
}

$end = time();

$taken = $end - $start;

echo "Took " . $taken . " Seconds" , PHP_EOL;

echo PHP_EOL;

$generated = count(array_unique($a));

if($generated >= $count){
    echo "No colissions", PHP_EOL;
}else{
    echo "Collisions, generated $generated instead of $count" , PHP_EOL;
}








/*
$obj = Munix::nextIdAsObject(5);

print_r($obj);
*/






