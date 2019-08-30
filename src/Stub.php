<?php

namespace Stub;

use Stub\Sources\GitHub;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    /**
     * Path to retrieve stub file(s).
     *
     * @var string
     */
    public $source;

    /**
     * Path or callback to output stub file(s).
     *
     * @var string|callable
     */
    public $output;

    /**
     * The successfully stubbed file(s).
     *
     * @var string[]
     */
    public $rendered = [];

    /**
     * The data to search / replace.
     *
     * @var string[]
     */
    public $variables = [];

    /**
     * Callback to prevent file(s) being stubbed.
     *
     * @var callable
     */
    private $filter;

    /**
     * Callback after successful stubbing.
     *
     * @var callable
     */
    private $listener;

    /**
     * Add .stub to filename when creating.
     *
     * @var string
     */
    private $appendFilename;

    /**
     *  If source is temporary.
     *
     * @var bool
     */
    private $staged = false;

    /**
     * The template tag opener.
     *
     * @var string
     */
    private $openTag = '{{';

    /**
     * The template tag closer.
     *
     * @var string
     */
    private $closeTag = '}}';

    /**
     * Path to stub's source file(s).
     *
     * @param string $path
     * @return $this
     */
    public function source(string $path)
    {
        $this->source = $this->getSourcePath($path);

        return $this;
    }

    /**
     * Path or callback to output stubs.
     *
     * @param string|callable $output
     * @return $this
     */
    public function output($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Compile the source to output.
     *
     * @param string[] $variables
     * @return $this
     */
    public function render(array $variables)
    {
        $this->variables = $variables;

        foreach ($this->files() as $file) {
            $this->handleOutput($file);
        }

        $this->unstage();

        return $this;
    }

    /**
     * Convert files into a stub
     *
     * @param string[] $variables
     * @return $this
     */
    public function create(array $variables)
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
     * Register a listen callback.
     *
     * @param callable $listener
     * @return $this
     */
    public function listen(callable $listener)
    {
        $this->listener = $listener;

        return $this;
    }

    /**
     * Register a filter callback.
     *
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Perform conversion on path.
     *
     * @param string $path
     * @return void
     */
    protected function handleOutput($path): void
    {
        $originalPath = $path;

        $content = $this->resolveContent($path);

        $path = $this->resolvePath($path);

        if ($this->isFiltered($path, $content)) {
            return;
        }

        if (is_callable(($this->output))) {
            ($this->output)($path, $content);

            return;
        }

        $path = $this->getOutputPath($path);

        $success = file_put_contents($path, $content) !== false;

        if (is_callable(($this->listener))) {
            ($this->listener)($path, $content, $success);
        }

        if ($this->staged) {
            unlink($originalPath);
        }

        if ($success) {
            $this->rendered[] = $path;
        }
    }

    /**
     * Replace variables in root path.
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath(string $path): string
    {
        $path = str_replace('.stub', '', $path);
        $path = str_replace($this->source, '', $path);
        $path = $this->replaceVariables($path);

        return ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Replace variables in content.
     *
     * @param string $path
     * @return string
     */
    protected function resolveContent(string $path): string
    {
        return $this->replaceVariables(file_get_contents($path));
    }

    /**
     * Replace known variables in a string.
     *
     * @param string $content
     * @return string
     */
    protected function replaceVariables(string $content = ''): string
    {
        foreach ($this->variables as $key => $value) {
            $search = "{$this->openTag}{$key}{$this->closeTag}";
            $content = str_replace($search, $value, $content);
        }

        return $content;
    }

    /**
     * Get or create source path.
     *
     * @param string $path
     * @return string
     */
    protected function getSourcePath(string $path): string
    {
        if (substr($path, 0, 1) == ':') {
            $path = str_replace(':', '', $path);
            $path = "https://github.com/awesome-stubs/$path";
        }

        if (substr($path, 0, 18) == 'https://github.com') {
            $path = (new GitHub($path))->path;
            $this->staged = true;
        }

        return $path;
    }

    /**
     * Get or create output path.
     *
     * @param string $path
     * @return string
     */
    protected function getOutputPath(string $path): string
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
     * Remove the last segment of a path.
     *
     * @param string $path
     * @return string
     */
    protected function getDirectory(string $path): string
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    /**
     * Sort array keys by length.
     *
     * @param array $array
     * @return array
     */
    protected function orderByKeyLength(array $array): array
    {
        $keys = array_map('strlen', array_keys($array));

        array_multisort($keys, SORT_DESC, $array);

        return $array;
    }

    /**
     * Determine if path fails callback.
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    protected function isFiltered(string $path, string $content): bool
    {
        return is_callable(($this->filter))
            && ($this->filter)($path, $content) === false;
    }

    /**
     * Remove the temp source directory.
     *
     * @return void
     */
    public function unstage(): void
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

        $this->source = null;
    }

    /**
     * Get files from the source directory.
     *
     * @return array
     */
    protected function files(): array
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
     * Change variable tags.
     *
     * @param string $open
     * @param string $close
     * @return $this
     */
    public function usingTags(string $open, string $close)
    {
        $this->openTag = $open;
        $this->closeTag = $close;

        return $this;
    }

    /**
     * Get stub.json values.
     *
     * @param string $path
     * @return array
     */
    public function settings(string $path): array
    {
        $path = "$path/stub.json";

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }

        throw new InvalidArgumentException("$path does not contain a stub.json");
    }
}
