<?php

namespace Tests\Formatters;

class MockStr
{
    public static function studly(string $value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    public static function snake(string $value, $delimiter = '_')
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));

        return $value;
    }

    public static function plural(string $value)
    {
        if (!preg_match('/(.*)s$/', $value)) {
            return $value.'s';
        }

        return $value;
    }

    public static function singular(string $value)
    {
        if (preg_match('/(.*)s$/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    public static function title(string $value)
    {
        return ucwords($value);
    }
}
