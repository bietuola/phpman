<?php
declare(strict_types=1);

namespace Core\Utils;

class Env
{
    /**
     * @var array The loaded environment data.
     */
    protected static array $data = [];

    /**
     * Loads environment variables from the specified file.
     *
     * @param string $file The path to the environment variable definition file.
     * @return void
     */
    public static function load(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        $env = parse_ini_file($file, true);
        self::set($env);
    }

    /**
     * Retrieves the value of a specific environment variable.
     *
     * @param string|null $name The name of the environment variable.
     * @param mixed|null $default The default value to return if the variable is not found.
     * @return mixed|null The value of the environment variable or the default value if not found.
     */
    public static function get(string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return self::$data;
        }
        $name = strtoupper(str_replace('.', '_', $name));
        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }
        return $default;
    }

    /**
     * Sets one or multiple environment variables.
     *
     * @param array|string $env The environment variable(s) to set.
     * @param mixed|null $value The value to set if a single variable is provided.
     * @return void
     */
    public static function set(array|string $env, mixed $value = null): void
    {
        if (is_array($env)) {
            // If an array is provided, process each element and set the corresponding environment variables.
            $env = array_change_key_case($env, CASE_UPPER);
            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    // Nested arrays are flattened by combining keys.
                    foreach ($val as $k => $v) {
                        self::$data[$key . '_' . strtoupper($k)] = trim($v);
                    }
                } else {
                    self::$data[$key] = trim($val);
                }
            }
        } else {
            // If a single variable is provided, set it with the specified value.
            $name = strtoupper(str_replace('.', '_', $env));
            self::$data[$name] = trim($value);
        }
    }
}