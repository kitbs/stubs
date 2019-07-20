<?php

namespace Stub;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Stub
{
    /**
     * The output path.
     * @var string
     */
    public $output;

    /**
     * Whether the output is a single file.
     * @var bool
     */
    public $fileOutput = false;

    /**
     * The source path.
     *
     * @var string
     */
    public static $path;

    /**
     * The method that will be used to parse the source path.
     *
     * @var string
     */
    public static $method;

    /**
     * The variables passed to the process.
     *
     * @var array
     */
    public $variables = [];

    /**
     * Set the source path and start a new process.
     *
     * @param  string $path The source path.
     * @return self
     */
    public static function source($path)
    {
        self::$path = static::realPath($path);

        self::$method = (is_dir($path)) ? 'parseDirectory' : 'parseFile';

        return new self;
    }

    /**
     * Set the output directory.
     *
     * @param  string  $path   The directory or file path to output the files.
     * @param  bool $isFile Whether the path provided is a single filename.
     * @return self
     */
    public function output($path, $isFile = false)
    {
        if (self::$method == 'parseDirectory' && $isFile) {
            throw new \InvalidArgumentException('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is a directory');
        } elseif (is_callable($path) && $isFile) {
            throw new \InvalidArgumentException('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is callable');
        }

        $this->output = static::realPath($path);
        $this->fileOutput = $isFile;

        return $this;
    }

    /**
     * Parse a directory recursively and handle the output.
     * @return void
     */
    public function parseDirectory()
    {
        foreach ($this->files() as $file) {
            $this->handleOutput(
                $this->resolvedPath($file),
                $this->resolvedContent($file)
           );
        }
    }

    /**
     * Parse a single file and handle the output.
     *
     * @return void
     */
    public function parseFile()
    {
        $this->handleOutput(
            $this->resolvedPath(self::$path),
            $this->resolvedContent(self::$path)
       );
    }

    /**
     * Set the variables to replace in the output files.
     *
     * @param  array $variables The variables to replace in the output files.
     *
     * @return self
     */
    public function variables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Run the output process.
     *
     * @return self
     */
    public function run()
    {
        $this->{self::$method}();

        return $this;
    }

    /**
     * Handle the output of a file.
     * @param  string $path    The file path to process.
     * @param  string $content The content of the file.
     * @return bool
     */
    protected function handleOutput(string $path, string $content)
    {
        $path = $this->getBasePath($path);

        if (is_callable(($this->output))) {
            return ($this->output)($path, $content);
        }

        $folder = $this->folder($path);

        if ($folder && !file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        return (bool) file_put_contents($path, $content);
    }

    /**
     * Resolve a path by replacing any variables.
     *
     * @param  string $path The path to resolve.
     *
     * @return string
     */
    protected function resolvedPath(string $path)
    {
        $path = str_replace('.stub', '', $path);

        return $this->replaceVariables($path);
    }

    /**
     * Resolve a file's content by replacing any variables.
     *
     * @param  string $path The file path
     *
     * @return string
     */
    protected function resolvedContent(string $path)
    {
        return $this->replaceVariables(file_get_contents($path));
    }

    /**
     * Replace the variables in the string.
     *
     * @param  string $content The content to replace variables in.
     *
     * @return string
     */
    protected function replaceVariables(string $content)
    {
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }

    /**
     * Get the folder of a file path.
     *
     * @param  string $path The file path
     *
     * @return string
     */
    protected function folder(string $path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        array_pop($segments);

        return implode(DIRECTORY_SEPARATOR, $segments);
    }

    /**
     * Get the base path of a file path.
     *
     * @param  string $path The file path
     *
     * @return string
     */
    public function getBasePath(string $path)
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

        return rtrim($this->replaceVariables($this->output), DIRECTORY_SEPARATOR) . ($filepath ? DIRECTORY_SEPARATOR . $filepath : null);
    }

    /**
     * The files to be processed.
     *
     * @param  bool $resolved Whether the paths should be resolved
     *
     * @return array
     */
    public function files($resolved = false)
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::$path
            )
        );

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                 $filepath = $file->getPathname();

                 if ($resolved) {
                     $filepath = $this->resolvedPath($filepath);
                 }

                 $files[] = $filepath;
            }
        }

        return $files;
    }

    /**
     * Expand the real path even if it does not exist.
     *
     * @link Adapted from https://www.php.net/manual/en/function.realpath.php#84012
     *
     * @param  string $path The path to expand.
     *
     * @return string
     */
    protected static function realPath(string $path)
    {
        $path = preg_replace('@[/\\\\]+@', DIRECTORY_SEPARATOR, $path);

        $parts = explode(DIRECTORY_SEPARATOR, $path);

        $absolutes = array();

        foreach ($parts as $part) {

            if ('.' == $part) continue;

            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
