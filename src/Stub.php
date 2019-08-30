<?php

namespace Stub;

use Stub\Sources\Github;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    /**
     * Path to retrieve stub file(s)
     *
     * @var string
     */
    public $source;

    /**
     * Path to output stub file(s)
     *
     * @var mixed
     */
    public $output;
    public $successful = 0;
    /**
     * The successly stubbed file(s)
     *
     * @var array
     */
    public $rendered = [];

    /**
     * The data to search / replace
     *
     * @var array
     */
    public $variables = [];

    /**
     * Callback to prevent file(s)
     *
     * @var callback
     */
    private $filter;

    /**
     * Callback after success
     *
     * @var callback
     */
    private $listener;

    /**
     * Add .stub to when create
     *
     * @var string
     */
    private $appendFilename;

    /**
     *  If source is temporary
     *
     * @var boolean
     */
    private $staged = false;

    /**
     * The template tag opener
     *
     * @var string
     */
    private $openTag = '{{';

    /**
     * The template tag closer
     *
     * @var string
     */
    private $closeTag = '}}';

    /**
     * Path to stub's source file(s)
     *
     * @param string $path
     * @return \Stub\Stub
     */
    public function source($path)
    {
        $path = $this->getSource($path);

        $this->source = $path;

        return $this;
    }

    /**
     * Path or callback to output stubs
     *
     * @param string|callback $output
     * @return \Stub\Stub
     */
    {
        $this->output = $path;

        return $this;
    }

    /**
     * Compile the source to output
     *
     * @param array $variables
     * @return \Stub\Stub
     */
    public function render($variables)
    {
        $this->variables = $variables;

        foreach ($this->files() as $file) {
            $this->handleOutput($file);
        }

        $this->unstage();

        return $this->successful;
    }

    /**
     * Convert files into a stub
     *
     * @param array $variables
     * @return \Stub\Stub
     */
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

    /**
     * Register a listen callback
     *
     * @param callback $listener
     * @return \Stub\Stub
     */
    public function listen(callable $listener)
    {
        $this->listener = $listener;

        return $this;
    }

    /**
     * Register a filter callback
     *
     * @param callback $filter
     * @return \Stub\Stub
     */
    public function filter(callable $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Perform conversion on path
     *
     * @param string $path
     * @return void
     */
    protected function handleOutput($path)
    {
        $originalPath = $path;

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

        if ($this->staged) {
            unlink($originalPath);
        }

        ($success) ? $this->successful++ : null;
    }

    /**
     * Replace variables in root path
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath($path)
    {
        $path = str_replace('.stub', '', $path);
        $path = str_replace($this->source, '', $path);
        $path = $this->replaceVariables($path);

        return ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Replace variables in content
     *
     * @param string $path
     * @return void
     */
    protected function resolveContent($path)
    {
        return $this->replaceVariables(file_get_contents($path));
    }

    /**
     * Replace known variables
     *
     * @param string $content
     * @return string
     */
    protected function replaceVariables($content = "")
    {
        foreach ($this->variables as $key => $value) {
            $search = "{$this->openTag}{$key}{$this->closeTag}";
            $content = str_replace($search, $value, $content);
        }

        return $content;
    }

    /**
     * Get or create source path
     *
     * @param string $path
     * @return string
     */
    /**
     * Get or create output path
     *
     * @param string $path
     * @return string
     */
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

    /**
     * Get last segment of a path
     *
     * @param string $path
     * @return string
     */
    protected function getDirectory($path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    /**
     * Longest array keys to top
     *
     * @param string $path
     * @return string
     */
    protected function orderByKeyLength($array)
    {
        $keys = array_map('strlen', array_keys($array));

        array_multisort($keys, SORT_DESC, $array);

        return $array;
    }

    /**
     * Determine if path fails callback
     *
     * @param string $path
     * @param string $content
     * @return boolean
     */
    protected function isFiltered($path, $content)
    {
        return is_callable(($this->filter))
            && ($this->filter)($path, $content) === false;
    }

    /**
     * Remove temp source directory
     *
     * @return void
     */
    public function unstage()
    {
        if (!$this->staged) {
            return;
        }

        $directories = new RecursiveDirectoryIterator($this->source);
        $directories->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        foreach ($directories as $directory) {
            rmdir($directory->getPathname());
        }

        if (file_exists("{$this->source}/stub.json")) {
            unlink("{$this->source}/stub.json");
        }

        rmdir($this->source);
    }

    /**
     * Get files from source directory
     *
     * @return array
     */
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

    /**
     * Change variable tags
     *
     * @param string $open
     * @param string $close
     * @return \Stub\Stub
     */
    public function usingTags($open, $close)
    {
        $this->openTag = $open;
        $this->closeTag = $close;

        return $this;
    }

    /**
     * Get stub.json values
     *
     * @param string $path
     * @return array
     */
    public function settings($path)
    {
        $path = "$path/stub.json";

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }

        throw new InvalidArgumentException("$path does not contain a stub.json");
    }
}
