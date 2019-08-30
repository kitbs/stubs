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
     * Construct a formatter and return the variables.
     * @param  string[]  $variables
     * @return string[]
     */
    public static function make(array $variables)
    {
        return (new static($variables))->all();
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

        $class = get_called_class();

        throw new ErrorException("Undefined variable: {$class}::\${$attribute}");
    }

    /**
     * Get a variable value as a magic method.
     * @param  string $name
     * @param array $attributes
     * @return string
     */
    public function __call(string $name, array $attributes)
    {
        if (!count($attributes) && isset($this->variables[$name])) {
            return $this->variables[$name];
        }

        $class = get_called_class();

        throw new BadMethodCallException("Call to undefined method: {$class}::{$name}()");
    }

    /**
     * Compute the variables.
     * @return string[]
     */
    final public function all()
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
        return $this->all();
    }
}
