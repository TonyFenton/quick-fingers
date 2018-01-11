<?php

$upSuffix = $argc > 1 ? $argv[1] : '__';
$downSuffix = $argc > 2 ? $argv[2] : '_'.date('Y-m-d');
$upCounter = 0;
$downCounter = 0;
$revertUp = [];
$revertDown = [];
$msg = '';

try {
    foreach (glob('*'.$upSuffix) as $element) {
        $name = substr($element, 0, -strlen($upSuffix));
        renameElement($name, $name.$downSuffix);
        $revertDown[] = $name;
        $downCounter++;
        renameElement($element, $name);
        $revertUp[] = $name;
        $upCounter++;
    }
    $msg = sprintf('Done, up: %d, down: %d.', $upCounter, $downCounter);
} catch (\Exception $e) {
    foreach ($revertUp as $name) {
        rename($name, $name.$upSuffix);
    }
    foreach ($revertDown as $name) {
        rename($name.$downSuffix, $name);
    }
    $msg = 'Abort, reverted to the initial state. '.$e->getMessage();
}

echo $msg.PHP_EOL;

function renameElement($oldname, $newname)
{
    if (!rename($oldname, $newname)) {
        throw new \RuntimeException('Can not rename "'.$oldname.'".');
    }

    return true;
}
