<?php

namespace Stub\Sources;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Github
{
    public $path;

    public function __construct($repository)
    {
        $this->path = uniqid('stub_');

        $url = "https://github.com/$repository/archive/master.zip";

        file_put_contents("{$this->path}/master.zip", file_get_contents($url));

        $zip = new ZipArchive;
        $zip->open("{$this->path}/master.zip");
        $zip->extractTo($this->path);
        $zip->close();

        unlink("{$this->path}/master.zip");
    }
}
