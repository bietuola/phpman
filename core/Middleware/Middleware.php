<?php
declare(strict_types=1);

namespace Core\Middleware;

use RuntimeException;
use function array_merge;
use function array_reverse;
use function is_array;
use function method_exists;

/**
 * Class Middleware
 *
 * Handles loading and retrieving middleware configurations for plugins and applications.
 *
 * @package Core\Middleware
 */
class Middleware
{
    /**
     * @var array Holds instances of loaded middleware configurations.
     */
    protected static array $instances = [];

    /**
     * Loads middleware configurations into the instances array.
     *
     * @param mixed $allMiddlewares The array containing all middleware configurations.
     * @param string $plugin The plugin name (optional).
     * @return void
     * @throws RuntimeException If the middleware configuration is invalid.
     */
    public static function load(mixed $allMiddlewares, string $plugin = ''): void
    {
        if (!is_array($allMiddlewares)) {
            return;
        }
        foreach ($allMiddlewares as $appName => $middlewares) {
            if (!is_array($middlewares)) {
                throw new RuntimeException('Bad middleware config');
            }
            if ($appName === '@') {
                $plugin = '';
            }
            if (str_contains($appName, 'plugin.')) {
                $explode = explode('.', $appName, 4);
                $plugin = $explode[1];
                $appName = $explode[2] ?? '';
            }
            foreach ($middlewares as $className) {
                if (method_exists($className, 'process')) {
                    static::$instances[$plugin][$appName][] = [$className, 'process'];
                } else {
                    // @todo Log or handle missing process method
                    echo "Middleware $className::process does not exist\n";
                }
            }
        }
    }

    /**
     * Retrieves middleware for a given plugin and application name.
     *
     * @param string $plugin The plugin name.
     * @param string $appName The application name.
     * @param bool $withGlobalMiddleware Whether to include global middleware.
     * @return array The array of middleware configurations.
     */
    public static function getMiddleware(string $plugin, string $appName, bool $withGlobalMiddleware = true): array
    {
        $globalMiddleware = static::$instances['']['@'] ?? [];
        $appGlobalMiddleware = $withGlobalMiddleware && isset(static::$instances[$plugin]['']) ? static::$instances[$plugin][''] : [];

        if ($appName === '') {
            return array_reverse(array_merge($globalMiddleware, $appGlobalMiddleware));
        }

        $appMiddleware = static::$instances[$plugin][$appName] ?? [];
        return array_reverse(array_merge($globalMiddleware, $appGlobalMiddleware, $appMiddleware));
    }

    /**
     * Deprecated method placeholder for container handling.
     *
     * @param mixed $_ Placeholder for container data.
     * @deprecated
     */
    public static function container(mixed $_)
    {
        // Deprecated method, no longer in use
    }
}
