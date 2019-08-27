<?php

namespace Tests;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PHPUnit\Framework\TestCase as BaseCase;

class TestCase extends BaseCase
{
    public function setUp(): void
    {
        chdir(__DIR__);
    }

    public function tearDown(): void
    {
        $dir = __DIR__.'/project';

        if (!is_dir($dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $remove = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $remove($fileinfo->getRealPath());
        }

        rmdir($dir);
    }
}
