<?php

namespace Tests\VariableSets;

use InvalidArgumentException;

use Stub\VariableSet;

class NovaTool extends VariableSet
{
    public function transform(): array
    {
        return [
            'package'          => $this->get(),
            'component'        => $this->toolName(),
            'title'            => $this->toolTitle(),
            'class'            => $this->toolClass(),
            'namespace'        => $this->toolNamespace(),
            'name'             => $this->toolName(),
            'escapedNamespace' => $this->escapedToolNamespace(),
        ];
    }

    public function validate()
    {
        if (stripos($this->get(), '/') === false) {
            throw new InvalidArgumentException("The base value expects a vendor and name in 'Composer' format. Here's an example: `vendor/name`.");
        }
    }

    protected function toolNamespace()
    {
        return $this->mockStrStudly($this->toolVendor()).'\\'.$this->toolClass();
    }

    protected function escapedToolNamespace()
    {
        return str_replace('\\', '\\\\', $this->toolNamespace());
    }

    protected function toolClass()
    {
        return $this->mockStrStudly($this->toolName());
    }

    protected function toolVendor()
    {
        return explode('/', $this->get())[0];
    }

    protected function toolTitle()
    {
        return $this->mockStrTitle(str_replace('-', ' ', $this->toolName()));
    }

    protected function toolName()
    {
        return explode('/', $this->get())[1];
    }

    protected function mockStrStudly(string $value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    protected function mockStrTitle(string $value)
    {
        return ucwords($value);
    }
}
