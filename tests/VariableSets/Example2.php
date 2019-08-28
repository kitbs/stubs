<?php

namespace Tests\VariableSets;

use Stub\VariableSet;

class Example2 extends VariableSet
{
    protected function transform()
    {
        return [
            'post_name'         => MockStr::singular($this->get()),
            'post_lower_plural' => MockStr::snake(MockStr::plural($this->get()), '-'),
        ];
    }
}
