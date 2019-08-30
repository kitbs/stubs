<?php

namespace Tests\Formatters;

use Stub\Formatter;

class WithHelpers extends Formatter
{
    public function name()
    {
        return MockStr::singular($this->name);
    }

    public function test_helper()
    {
        return $this->helper();
    }

    public function test_helper_with_argument()
    {
        return $this->helper_with_argument('ARGUMENT');
    }

    protected function helper()
    {
        return '__HELPER__';
    }

    protected function helper_with_argument(string $argument)
    {
        return '__HELPER:'.$argument.'__';
    }
}
