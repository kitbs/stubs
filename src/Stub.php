<?php

namespace Stub;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    public $source;
    public $output;
    public $variables = [];
    public $openTag = '{{';
    public $closeTag = '}}';
    public $appendFilename;

    public function source($path)
    {
        $this->source = $path;

        return $this;
    }

    public function output($path)
    {
        $this->output = $path;

        return $this;
    }

    public function render($variables)
    {
        $this->variables = $variables;

        foreach ($this->files() as $file) {
            $this->handleOutput($file, $this->resolveContent($file));
        }
    }

    public function create($variables)
    {
        $variables = $this->orderByKeyLength($variables);

        foreach ($variables as $key => $value) {
            unset($variables[$key]);
            $variables[$key] = "{$this->openTag}{$value}{$this->closeTag}";
        }

        $this->appendFilename = '.stub';

        $this->usingTags('', '');
        $this->render($variables);
    }

    protected function handleOutput($path, $content)
    {
        $path = str_replace($this->source, '', $path);
        $path = $this->resolvePath($path);
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        if (is_callable(($this->output))) {
            return ($this->output)($path, $content);
        }

        $path = $this->output . DIRECTORY_SEPARATOR . $path;

        $path = $path . $this->appendFilename;

        $directory = $this->getDirectory($path);

        if ($directory && ! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        return file_put_contents($path, $content);
    }

    protected function resolvePath($path)
    {
        $path = str_replace('.stub', '', $path);

        return $this->variables($path);
    }

    protected function resolveContent($path)
    {
        return $this->variables(file_get_contents($path));
    }

    protected function variables($content = "")
    {
        foreach ($this->variables as $key => $value) {
            $search = "{$this->openTag}{$key}{$this->closeTag}";
            $content = str_replace($search, $value, $content);
        }

        return $content;
    }

    protected function getDirectory($path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    protected function orderByKeyLength($array)
    {
        $keys = array_map('strlen', array_keys($array));

        array_multisort($keys, SORT_DESC, $array);

        return $array;
    }

    protected function files()
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->source
            )
        );

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function usingTags($open, $close)
    {
        $this->openTag = $open;
        $this->closeTag = $close;

        return $this;
    }
}
