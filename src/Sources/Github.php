<?php

namespace Stub\Sources;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Github
{
    public $files = [];

    public function __construct($repository)
    {
        $this->staged = uniqid('stub_');

        $url = "https://github.com/$repository/archive/master.zip";

        file_put_contents("{$this->staged}/master.zip", file_get_contents($url));

        $zip = new ZipArchive;
        $zip->open("{$this->staged}/master.zip");
        $zip->extractTo($this->staged);
        $zip->close();

        unlink("{$this->staged}/master.zip");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->staged)
        );

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $this->files[] = $file->getPathname();
            }
        }

        // has to add a $this->staged to Stub class
        // so class can clean up after running
        //--------------------------------------------
        // foreach ($iterator as $file) {
        //     $remove = ($file->isDir() ? 'rmdir' : 'unlink');
        //     $remove($fileinfo->getRealPath());
        // }
    }
}
