<?php

namespace Stub;

use ErrorException;
use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;

abstract class Formatter
{
    /**
     * The base variables.
     * @var string[]
     */
    protected $variables = [];

    /**
     * Construct the formatter.
     * @param string[] $variables
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Construct a formatter and return the variables.
     * @param  string[]  $variables
     * @return string[]
     */
    final public static function make(array $variables)
    {
        return (new static($variables))->format();
    }

    /**
     * Get a variable value.
     * @param  string $attribute
     * @return string
     */
    final public function __get(string $attribute)
    {
        return $this->get($attribute);
    }

    /**
     * Get a variable value as a magic method.
     * @param  string $name
     * @param array $attributes
     * @return string
     */
    final public function __call(string $name, array $attributes)
    {
        if (!count($attributes) && $this->has($name)) {
            return $this->get($name);
        }

        $class = get_called_class();

        throw new BadMethodCallException("Call to undefined method: {$class}::{$name}()");
    }

    /**
     * Compute the variables.
     * @return string[]
     */
    final public function format()
    {
        $this->validate();

        $computed = $this->variables;

        $class = Formatter::class;

        $methods = (new ReflectionClass(get_called_class()))
            ->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($methods, function (ReflectionMethod $method) use ($class) {
            return $method->class != $class && $method->getNumberOfParameters() == 0;
        });

        foreach ($methods as $method) {
            $computed[$method->name] = $method->invoke($this);
        }

        return $computed;
    }

    /**
     * Return the original variables only.
     * @return string[]
     */
    final public function original()
    {
        return $this->variables;
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
     * Whether the variable exists.
     *
     * @param  string $variable
     * @return bool
     */
    final public function has($variable): bool
    {
        return array_key_exists($variable, $this->variables);
    }

    /**
     * Get a variable.
     *
     * @param string $variable
     * @return string
     */
    final public function get(string $variable)
    {
        if ($this->has($variable)) {
            return $this->variables[$variable];
        }

        $class = get_called_class();

        throw new ErrorException("Undefined variable: {$class}::\${$variable}");
    }
}
