<?php

$upSuffix = $argc > 1 ? $argv[1] : '__';
$downSuffix = $argc > 2 ? $argv[2] : '_'.date('Y-m-d');
$tmpSuffix = uniqid();
$upCounter = 0;
$downCounter = 0;
$log = [];
$msg = '';

try {
    foreach (glob('*'.$upSuffix) as $element) {
        $name = substr($element, 0, -strlen($upSuffix));
        renameElement($name, $name.$tmpSuffix, $log);
        $downCounter++;
        renameElement($element, $name, $log);
        $upCounter++;
        renameElement($name.$tmpSuffix, $name.$downSuffix, $log);
    }
    $msg = sprintf('Done, up: %d, down: %d.', $upCounter, $downCounter);
} catch (\Exception $e) {
    krsort($log);
    foreach ($log as $revert) {
        rename($revert[1], $revert[0]);
    }
    $msg = 'Abort, reverted to the initial state. '.$e->getMessage();
}

echo $msg.PHP_EOL;

function renameElement($oldname, $newname, &$log)
{
    if (!rename($oldname, $newname)) {
        throw new \RuntimeException('Can not rename "'.$oldname.'".');
    }
    $log[] = [$oldname, $newname];

    return true;
}
