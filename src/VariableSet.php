<?php

namespace Stub;

use JsonSerializable;

abstract class VariableSet implements JsonSerializable
{
    /**
     * The base value.
     * @var string
     */
    protected $base;

    /**
     * Additional variables.
     * @var array
     */
    protected $variables = [];

    /**
     * Construct the variable set from a base value.
     * @param string $base  The base value
     * @param array  $variables  Additional variables
     */
    public function __construct(string $base, array $variables = [])
    {
        $this->base = $base;

        foreach ($variables as $key => $value) {
            $this->variables[$key] = $value;
        }
    }

    /**
     * Make a variable set from a base value.
     * @param string $base  The base value
     * @param array  $variables  Additional variables
     */
    public static function make(string $base, array $variables = [])
    {
        return new static($base, $variables);
    }

    /**
     * Get a variable.
     * @param  string|null $key  The variable to return or null for the base value
     * @return string|null
     */
    protected function get(string $key = null)
    {
        return $key ? $this->variables[$key] ?? null : $this->base;
    }

    /**
	 * Specify data which should be serialized to JSON.
	 * @return array
	 */
    public function jsonSerialize()
    {
        return $this->values();
    }

    /**
     * Return all of the values for the variable set.
     * @return array
     */
    public function values(): array
    {
        $this->validate();

        return array_merge($this->transform(), $this->variables);
    }

    /**
     * Validate any values passed to the variable set.
     * @throws \InvalidArgumentException
     * @return void
     */
    public function validate()
    {
        //
    }

    /**
     * Transform the base value into the variable set.
     * @return array
     */
    abstract public function transform(): array;
}
