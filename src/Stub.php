<?php

namespace Stub;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    public $source;
    public $output;
    public $successful = 0;
    public $variables = [];
    public $openTag = '{{';
    public $closeTag = '}}';
    public $appendFilename;
    public $filter;
    public $listener;

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
            $this->handleOutput($file);
        }

        return $this->successful;
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

        return $this->render($variables);
    }

    public function listen(callable $listener)
    {
        $this->listener = $listener;

        return $this;
    }

    public function filter(callable $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    protected function handleOutput($path)
    {
        $content = $this->resolveContent($path);

        $path = $this->resolvePath($path);

        if ($this->isFiltered($path, $content)) {
            return false;
        }

        if (is_callable(($this->output))) {
            return ($this->output)($path, $content);
        }

        $path = $this->getOutputPath($path);

        $success = file_put_contents($path, $content) !== false;

        if (is_callable(($this->listener))) {
            ($this->listener)($path, $content, $success);
        }

        ($success) ? $this->successful++ : null;
    }

    protected function resolvePath($path)
    {
        $path = str_replace('.stub', '', $path);
        $path = str_replace($this->source, '', $path);
        $path = $this->replaceVariables($path);

        return ltrim($path, DIRECTORY_SEPARATOR);
    }

    protected function resolveContent($path)
    {
        return $this->replaceVariables(file_get_contents($path));
    }

    protected function replaceVariables($content = "")
    {
        foreach ($this->variables as $key => $value) {
            $search = "{$this->openTag}{$key}{$this->closeTag}";
            $content = str_replace($search, $value, $content);
        }

        return $content;
    }

    protected function getOutputPath($path)
    {
        $path = $this->output . DIRECTORY_SEPARATOR . $path;

        $path = $path . $this->appendFilename;

        $directory = $this->getDirectory($path);

        if ($directory && ! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        return $path;
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

    protected function isFiltered($path, $content)
    {
        return is_callable(($this->filter))
            && ($this->filter)($path, $content) === false;
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
