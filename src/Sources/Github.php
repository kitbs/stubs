<?php

namespace Stub\Sources;

use ZipArchive;
use RecursiveDirectoryIterator;

class Github
{
    public $path;

    public function __construct($url)
    {
        $this->path = uniqid('stub_');

        $zipPath = "{$this->path}/master.zip";

        $repository = str_replace('https://github.com/', '', $url);
        $url = "https://github.com/$repository/archive/master.zip";

        mkdir($this->path);

        file_put_contents($zipPath, file_get_contents($url));

        list($username, $project) = explode('/', $repository);

        $zip = new ZipArchive;
        $zip->open($zipPath);

        // Remove nested folder
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $path = $zip->getNameIndex($i);
            $zip->extractTo($this->path, [$path]);
            $cleaned = str_replace("$project-master/", '', $path);
            if ($cleaned) {
                rename("$this->path/$path", "$this->path/$cleaned");
            }
        }

        $zip->close();

        unlink("{$this->path}/master.zip");

        // remove empty folders from ./repository-master
        $directories = new RecursiveDirectoryIterator("{$this->path}/$project-master/");
        $directories->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        foreach ($directories as $directory) {
            rmdir($directory->getPathname());
        }

        rmdir("{$this->path}/$project-master/");
    }
}
