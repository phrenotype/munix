<?php

use Munix\Munix;

require "Munix/Munix.php";

$start  = time();

$values = [];

foreach (range(0, 10000) as $n) {
    //if ((time() - $start) < 1) {
        $id = Munix::nextId();
        $values[] = $id;
        echo $id . PHP_EOL;
    //} else {
        //break;
    //}
}


echo "Generated ", count(array_unique($values)), " ids in 1 second.", PHP_EOL;
