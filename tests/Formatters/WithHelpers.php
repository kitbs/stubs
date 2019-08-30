<?php

namespace Tests\Formatters;

use Stub\Formatter;

class WithHelpers extends Formatter
{
    public function name()
    {
        return MockStr::singular($this->name);
    }

    protected function helper()
    {
        return '__HELPER__';
    }

    protected function helper_argument(string $argument)
    {
        return '__HELPER_ARGUMENT:'.$argument.'__';
    }
}
