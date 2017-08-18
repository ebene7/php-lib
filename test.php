<?php

require __DIR__ . '/vendor/autoload.php';

use E7\Date\DateRange;
use E7\Utility\Range\Normalizer;
use E7\Utility\Range\Collection;

function printRanges($ranges) {
    foreach ($ranges as $range) {
        echo "$range\n";
    }
}

$ranges = new Collection([
    new DateRange('2017-01-01', '2017-03-31'),
    new DateRange('2017-04-01', '2017-05-31'),
    new DateRange('2017-02-01', '2017-03-15'),
    new DateRange('2017-07-01', '2017-07-15'),
    new DateRange('2017-08-01', '2017-09-30'),
    new DateRange('2017-09-01', '2017-10-31'),
]);


//printRanges($ranges);

$norm = new Normalizer();
$ranges2 = $norm->normalize($ranges);

//echo '---' . PHP_EOL;
//printRanges($ranges2);

print_r($ranges->toArray());






//convert modi
//
//  - touch/overlap
//
//  |================================================================================================|
//      |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//
//      |-------|                                   |-----------------------|
//               |-----------|   |----||----|
//         |--------|                           |-------------|
//
//Full (overlap)
//      |--------------------|   |----||----|   |---------------------------|
//
//Full (touch)
//      |--------------------|   |----------|   |---------------------------|
//
//Split
//      |-||----||--||-------|   |----||----|   |--||---------||------------|
//
//Gap
//  |--|                      |-|            |-|                             |-----------------------|
//
//Intersect
//        |-----||--|                               |---------|
