<?php

$upSuffix = $argc > 1 ? $argv[1] : '__';
$downSuffix = $argc > 2 ? $argv[2] : '_'.date('Y-m-d');
$upCounter = 0;
$downCounter = 0;

foreach (glob('*'.$upSuffix) as $element) {
    $name = substr($element, 0, -strlen($upSuffix));
    if (rename($name, $name.$downSuffix)) {
        $upCounter++;
    }
    if (rename($element, $name)) {
        $downCounter++;
    }
}

echo sprintf('Done, up: %d, down: %d.', $upCounter, $downCounter);
