<?php

namespace Tests\VariableSets;

use InvalidArgumentException;

use Stub\VariableSet;

class View extends VariableSet
{
    public function transform(): array
    {
        return [
            'name'         => $this->mockStrSingular($this->get()),
            'lower_plural' => $this->mockStrSnake($this->mockStrPlural($this->get()), '-'),
        ];
    }

    protected function mockStrStudly(string $value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    protected function mockStrSnake(string $value, $delimiter = '_')
    {
        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    protected function mockStrPlural(string $value)
    {
        if (!preg_match('/(.*)s$/', $value)) {
            return $value.'s';
        }

        return $value;
    }

    protected function mockStrSingular(string $value)
    {
        if (preg_match('/(.*)s$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
