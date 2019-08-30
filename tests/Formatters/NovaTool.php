<?php

namespace Tests\Formatters;

use InvalidArgumentException;

use Stub\Formatter;

class NovaTool extends Formatter
{
    protected function validate()
    {
        if (stripos($this->package, '/') === false) {
            throw new InvalidArgumentException("The `package` variable expects a vendor and name in 'Composer' format. Here's an example: `vendor/name`.");
        }
    }

    public function namespace()
    {
        return MockStr::studly($this->vendor()).'\\'.$this->class();
    }

    public function escapedNamespace()
    {
        return str_replace('\\', '\\\\', $this->namespace());
    }

    public function class()
    {
        return MockStr::studly($this->name());
    }

    public function vendor()
    {
        return explode('/', $this->package)[0];
    }

    public function title()
    {
        return MockStr::title(str_replace('-', ' ', $this->name()));
    }

    public function component()
    {
        return $this->name();
    }

    public function name()
    {
        return explode('/', $this->package)[1];
    }
}
