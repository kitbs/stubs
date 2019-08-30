<?php

namespace Stub;

abstract class Formatter
{
    /**
     * The base variables.
     * @var string[]
     */
    public $variables;

    /**
     * Construct the formatter.
     * @param string[] $variables
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Get a variable value.
     * @param  string $attribute
     * @return string
     */
    public function __get(string $attribute)
    {
        if (isset($this->variables[$attribute])) {
            return $this->variables[$attribute];
        }
    }

    /**
     * Compute the variables.
     * @return string[]
     */
    public function compute()
    {
        $this->validate();

        $computed = $this->variables;

        $methods = array_diff(
            get_class_methods(get_called_class()),
            get_class_methods(get_class())
        );

        print_r($methods);die;

        foreach ($methods as $method) {
            $computed[$method] = $this->$method();
        }

        return $computed;
    }

    /**
     * Validate any values passed to the variable set.
     *
     * Override this method in your own class and throw an
     * InvalidArgumentException if any expected variable is missing
     * or is not in the required format.
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function validate()
    {
        //
    }

    /**
     * Invoke the formatter and compute the variables.
     * @return string[]
     */
    public function __invoke()
    {
        return $this->compute();
    }
}
