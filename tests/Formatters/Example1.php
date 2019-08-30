<?php

namespace Tests\Formatters;

use Stub\Formatter;

class Example1 extends Formatter
{
    public function name()
    {
        return MockStr::singular($this->name);
    }

    public function lower_plural()
    {
        return MockStr::snake(MockStr::plural($this->name), '-');
    }
}
