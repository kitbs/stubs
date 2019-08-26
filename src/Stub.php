<?php

namespace Stub;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    public $output;
    public $fileOutput;
    public static $path;
    public static $method;
    public $variables = [];
    public $openTag = '{{';
    public $closeTag = '}}';

    public static function source($path)
    {
        self::$path = $path;

        self::$method = (is_dir($path)) ? 'parseDirectory' : 'parseFile';

        return new self;
    }

    public function output($path, $isFile = false)
    {
        if (self::$method == 'parseDirectory' && $isFile) {
            throw new \InvalidArgumentException('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is a directory');
        } elseif (is_callable($path) && $isFile) {
            throw new \InvalidArgumentException('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is callable');
        }
        
        $this->output = $path;
        $this->fileOutput = $isFile;

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
        $path = $this->getBasePath($path);

        if (is_callable(($this->output))) {
            return ($this->output)($path, $content);
        }

        $folder = $this->folder($path);

        if ($folder && !file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        return file_put_contents($path, $content);
    }

    protected function resolvedPath($path)
    {
        $path = str_replace('.stub', '', $path);

        return $this->variables($path);
    }

    protected function resolvedContent($path)
    {
        return $this->variables(file_get_contents($path));
    }

    protected function variables($content = "")
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{$this->openTag}{$key}{$this->closeTag}", $value, $content);
        }

        return $content;
    }

    protected function folder($path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    public function getBasePath($path)
    {
        if ($this->fileOutput && self::$method == 'parseFile') {
            $path = $this->folder($path);
        }

        $target_path = self::$path;

        if (!$this->fileOutput && self::$method == 'parseFile') {
            $target_path = dirname($target_path);
        }

        $filepath_parts = explode(DIRECTORY_SEPARATOR, $path);
        $folder_parts = explode(DIRECTORY_SEPARATOR, $target_path);

        $common = array_intersect_assoc($filepath_parts, $folder_parts);

        foreach ($common as $index => $segment) {
            unset($filepath_parts[$index]);
        }

        $filepath = join(DIRECTORY_SEPARATOR, $filepath_parts);

        if (is_callable($this->output)) {
            return $filepath;
        }

        return rtrim($this->variables($this->output), DIRECTORY_SEPARATOR) . ($filepath ? DIRECTORY_SEPARATOR . $filepath : null);
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

    public function usingTags($open, $close)
    {
        $this->openTag = $open;
        $this->closeTag = $close;

        return $this;
    }
}
