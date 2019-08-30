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
        $this->merge($variables);
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
        return $this->get($attribute);
    }

    /**
     * Set a variable value.
     * @param  string $attribute
     * @param  string $value
     * @return $this
     */
    public function __set(string $attribute, string $value)
    {
        return $this->set($attribute, $value);
    }

    /**
     * Get a variable value as a magic method.
     * @param  string $name
     * @param array $attributes
     * @return string
     */
    public function __call(string $name, array $attributes)
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

    /**
     * Whether the variable exists.
     *
     * @param  string $variable
     * @return bool
     */
    public function has($variable): bool
    {
        return array_key_exists($variable, $this->variables);
    }

    /**
     * Get a variable.
     *
     * @param string $variable
     * @return string
     */
    public function get(string $variable)
    {
        if ($this->has($variable)) {
            return $this->variables[$variable];
        }

        $class = get_called_class();

        throw new ErrorException("Undefined variable: {$class}::\${$variable}");
    }

    /**
     * Set a variable.
     *
     * @param string $variable
     * @param string $value
     * @return $this
     */
    public function set(string $variable, string $value)
    {
        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Remove a variable.
     *
     * @param string $variable
     * @return $this
     */
    public function unset(string $variable)
    {
        unset($this->variables[$variable]);

        return $this;
    }

    /**
     * Merge variables into the existing variables.
     *
     * @param string[] $variables
     * @return $this
     */
    public function merge(array $variables)
    {
        foreach ($variables as $variable => $value) {
            $this->variables[$variable] = $value;
        }

        return $this;
    }

    /**
     * Replace existing variables with new variables.
     *
     * @param string[] $variables
     * @return $this
     */
    public function replace(array $variables)
    {
        $this->variables = [];

        return $this->merge($variables);
    }
}
