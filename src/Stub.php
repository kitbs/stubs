<?php

namespace Stub;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    public $output;
    public static $path;
    public static $method;
    public $variables = [];

    public static function source($path)
    {
        self::$path = $path;

        self::$method = (is_dir($path)) ? 'parseDirectory' : 'parseFile';

        return new self;
    }

    public function output($path)
    {
        $this->output = $path;

        return $this;
    }

    public function parseDirectory()
    {
        foreach ($this->files() as $file) {
            $this->handleOutput(
                $this->resolvedPath($file),
                $this->resolvedContent($file)
           );
        }
    }

    public function parseFile()
    {
        $this->handleOutput(
            $this->resolvedPath(self::$path),
            $this->resolvedContent(self::$path)
       );
    }

    public function parse($variables)
    {
        $this->variables = $variables;

        $this->{self::$method}();

        return $this;
    }

    protected function handleOutput($path, $content)
    {
        if (is_callable(($this->output))) {
            return ($this->output)($path, $content);
        }

        $path = $this->getUniqueFolderStructure($path);

        if (!file_exists($this->folder($path))) {
            mkdir($this->folder($path), 0777, true);
        }

        return file_put_contents($path, $content);
    }

    protected function resolvedPath($path)
    {
        $path = str_replace('.stub', '', $path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        return $this->variables($path);
    }

    protected function resolvedContent($path)
    {
        return $this->variables(file_get_contents($path));
    }

    protected function variables($content = "")
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }

    protected function folder($path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    public function getUniqueFolderStructure($path)
    {
        $filepath_parts = explode(DIRECTORY_SEPARATOR, $path);
        $folder_parts = explode(DIRECTORY_SEPARATOR, self::$path);

        $common = array_intersect($filepath_parts, $folder_parts);

        foreach ($common as $index => $segment) {
            unset($filepath_parts[$index]);
        }

        $filepath = join(DIRECTORY_SEPARATOR, $filepath_parts);

        return rtrim($this->output, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filepath;
    }

    protected function files()
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::$path
            )
        );

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
