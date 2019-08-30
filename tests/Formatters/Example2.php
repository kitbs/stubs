<?php

namespace Tests\Formatters;

use Stub\Formatter;

class Example2 extends Formatter
{
    public function post_name()
    {
        return MockStr::singular($this->name);
    }

    public function post_lower_plural()
    {
        return MockStr::snake(MockStr::plural($this->name), '-');
    }
}
