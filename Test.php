<?php

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    const TEST_DIR = 'test';

    const IN_TEST_DIR = self::TEST_DIR.DIRECTORY_SEPARATOR;

    const SCRIPT = 'quick-fingers.php';

    protected function setUp()
    {
        $this->createTestData(self::TEST_DIR);
        $this->createTestData(self::TEST_DIR, 'index.php');
        $this->createTestData(self::IN_TEST_DIR.'public');
        $this->createTestData(self::IN_TEST_DIR.'src');
        $this->createTestData(self::IN_TEST_DIR.'var');
        $this->createTestData(self::IN_TEST_DIR.'templates');
    }

    public function test_success()
    {
        $this->createTestData(self::TEST_DIR, 'index.php__');
        $this->createTestData(self::IN_TEST_DIR.'src__', 'new.php');
        $this->createTestData(self::IN_TEST_DIR.'public__', 'new.php');

        $result = $this->execScript('Done, up: 3, down: 3.');

        $today = date('Y-m-d');
        $expected = [
            self::IN_TEST_DIR.'index.php',
            self::IN_TEST_DIR.'index.php_'.$today,
            self::IN_TEST_DIR.'old.php',
            self::IN_TEST_DIR.'public'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'public_'.$today.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'src_'.$today.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'templates'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'var'.DIRECTORY_SEPARATOR.'old.php',
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_revert()
    {
        $this->createTestData(self::IN_TEST_DIR.'src-new', 'new.php');
        $this->createTestData(self::IN_TEST_DIR.'www-new', 'new.php');

        $result = $this->execScript('Abort, reverted to the initial state. Can not rename "www".', ['-new', '-old']);

        $expected = [
            self::IN_TEST_DIR.'index.php',
            self::IN_TEST_DIR.'old.php',
            self::IN_TEST_DIR.'public'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src-new'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'templates'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'var'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'www-new'.DIRECTORY_SEPARATOR.'new.php',
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_replace()
    {
        $this->createTestData(self::IN_TEST_DIR.'public--', 'new.php');
        $this->createTestData(self::TEST_DIR, 'index.php--');

        $result = $this->execScript('Done, up: 2, down: 2.', ['--', '--']);

        $expected = [
            self::IN_TEST_DIR.'index.php',
            self::IN_TEST_DIR.'index.php--',
            self::IN_TEST_DIR.'old.php',
            self::IN_TEST_DIR.'public'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'public--'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'templates'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'var'.DIRECTORY_SEPARATOR.'old.php',
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_revertReplace()
    {
        $this->createTestData(self::TEST_DIR, 'index.php_test');
        $this->createTestData(self::IN_TEST_DIR.'src_test', 'new.php');
        $this->createTestData(self::IN_TEST_DIR.'templates_test', 'new.php');
        $this->createTestData(self::IN_TEST_DIR.'www_test', 'new.php');

        $result = $this->execScript('Abort, reverted to the initial state. Can not rename "www".', ['_test', '_test']);

        $expected = [
            self::IN_TEST_DIR.'index.php',
            self::IN_TEST_DIR.'index.php_test',
            self::IN_TEST_DIR.'old.php',
            self::IN_TEST_DIR.'public'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'src_test'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'templates'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'templates_test'.DIRECTORY_SEPARATOR.'new.php',
            self::IN_TEST_DIR.'var'.DIRECTORY_SEPARATOR.'old.php',
            self::IN_TEST_DIR.'www_test'.DIRECTORY_SEPARATOR.'new.php',
        ];

        $this->assertEquals($expected, $result);
    }

    private function getDirArr(string $dir = self::TEST_DIR, array &$arr = [])
    {
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') as $path) {
            if (is_dir($path)) {
                $this->getDirArr($path, $arr);
            } else {
                $arr[] = $path;
            }
        }

        return $arr;
    }

    public function tearDown(string $dir = self::TEST_DIR)
    {
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') as $path) {
            if (is_dir($path)) {
                $this->tearDown($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function createTestData(string $dir, string $file = 'old.php')
    {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        fopen($dir.DIRECTORY_SEPARATOR.$file, 'w');
    }

    private function execScript(string $expectedOutput, array $params = []): array
    {
        copy(self::SCRIPT, self::IN_TEST_DIR.self::SCRIPT);
        chdir(self::TEST_DIR);
        $output = [];
        exec('php '.self::SCRIPT.' '.implode(' ', $params), $output);
        $this->assertSame($expectedOutput, $output[count($output) - 1]);
        chdir('..');
        unlink(self::IN_TEST_DIR.self::SCRIPT);

        return $this->getDirArr();
    }
}
