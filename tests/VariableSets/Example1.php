<?php

namespace Tests\VariableSets;

use Stub\VariableSet;

class Example1 extends VariableSet
{
    protected function transform()
    {
        return [
            'name'         => MockStr::singular($this->get()),
            'lower_plural' => MockStr::snake(MockStr::plural($this->get()), '-'),
        ];
    }
}
