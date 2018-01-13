<?php

$quickFingers = new QuickFingers(
    $argc > 2 ? $argv[2] : '_'.date('Y-m-d'),
    $argc > 1 ? $argv[1] : '__'
);
echo $quickFingers->exec().PHP_EOL;

class QuickFingers
{
    /** @var string */
    private $downSuffix;

    /** @var string */
    private $upSuffix;

    /** @var string */
    private $tmpSuffix;

    /** @var array */
    private $renameLog = [];

    /**
     * @param string $downSuffix
     * @param string $upSuffix
     */
    public function __construct($downSuffix, $upSuffix)
    {
        $this->downSuffix = $downSuffix;
        $this->upSuffix = $upSuffix;
        $this->tmpSuffix = uniqid();
    }

    /**
     * @return string
     */
    public function exec()
    {
        try {
            $msg = $this->run();
        } catch (\Exception $e) {
            $msg = $this->revert($e->getMessage());
        }

        return $msg;
    }

    /**
     * @return string
     */
    private function run()
    {
        $counter = ['up' => 0, 'down' => 0];
        foreach (glob('*'.$this->upSuffix) as $element) {
            $name = substr($element, 0, -strlen($this->upSuffix));
            $this->rename($name, $name.$this->tmpSuffix);
            $counter['down']++;
            $this->rename($element, $name);
            $counter['up']++;
            $this->rename($name.$this->tmpSuffix, $name.$this->downSuffix);
        }

        return sprintf('Done, up: %d, down: %d.', $counter['up'], $counter['down']);
    }

    /**
     * @param string $errorMsg
     * @return string
     */
    private function revert($errorMsg)
    {
        krsort($this->renameLog);
        foreach ($this->renameLog as $rename) {
            rename($rename[1], $rename[0]);
        }
        $this->renameLog = [];

        return 'Abort, reverted to the initial state. '.$errorMsg;
    }

    /**
     * @param string $oldname
     * @param string $newname
     */
    private function rename($oldname, $newname)
    {
        if (!rename($oldname, $newname)) {
            throw new \RuntimeException('Can not rename "'.$oldname.'".');
        }
        $this->renameLog[] = [$oldname, $newname];
    }
}
