<?php

namespace Tests\Formatters;

use Stub\Formatter;

class Example2 extends Formatter
{
    public function singular_name()
    {
        return MockStr::singular($this->name);
    }

    public function lower_plural()
    {
        return MockStr::snake(MockStr::plural($this->name), '-');
    }
}
