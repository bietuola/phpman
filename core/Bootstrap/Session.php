<?php
declare(strict_types=1);

namespace Core\Bootstrap;

use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;
use function config;
use function property_exists;

class Session implements BootstrapInterface
{
    /**
     * Starts the session handling with the given worker.
     *
     * This method configures the session settings using the configuration
     * provided in the application's configuration file. It sets session
     * parameters such as the session name, handler class, and other relevant
     * settings that control session behavior.
     *
     * @param Worker|null $worker The worker instance, or null if not applicable.
     *
     * @return void
     */
    public static function start(?Worker $worker): void
    {
        // Retrieve session configuration
        $sessionConfig = config('session');

        // Set session name
        self::setSessionName($sessionConfig['session_name']);

        // Set session handler class and its configuration
        SessionBase::handlerClass($sessionConfig['handler'], $sessionConfig['config'][$sessionConfig['type']]);

        // Apply other session configuration settings
        self::applySessionSettings($sessionConfig);
    }

    /**
     * Set the session name.
     *
     * @param string $sessionName The name of the session.
     *
     * @return void
     */
    private static function setSessionName(string $sessionName): void
    {
        if (property_exists(SessionBase::class, 'name')) {
            SessionBase::$name = $sessionName;
        } else {
            Http::sessionName($sessionName);
        }
    }

    /**
     * Apply session configuration settings.
     *
     * @param array $config The session configuration array.
     *
     * @return void
     */
    private static function applySessionSettings(array $config): void
    {
        // Map of configuration keys to SessionBase properties
        $configMap = [
            'auto_update_timestamp' => 'autoUpdateTimestamp', // Automatically update timestamp
            'cookie_lifetime'       => 'cookieLifetime',      // Lifetime of the session cookie
            'gc_probability'        => 'gcProbability',       // Probability of garbage collection
            'cookie_path'           => 'cookiePath',          // Path for the session cookie
            'http_only'             => 'httpOnly',            // HttpOnly flag for the session cookie
            'same_site'             => 'sameSite',            // SameSite attribute for the session cookie
            'lifetime'              => 'lifetime',            // Session lifetime
            'domain'                => 'domain',              // Domain for the session cookie
            'secure'                => 'secure',              // Secure flag for the session cookie
        ];

        // Apply each configuration setting if it exists in the config array
        foreach ($configMap as $configKey => $propertyName) {
            if (isset($config[$configKey]) && property_exists(SessionBase::class, $propertyName)) {
                SessionBase::${$propertyName} = $config[$configKey];
            }
        }
    }
}
