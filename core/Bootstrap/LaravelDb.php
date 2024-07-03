<?php
declare(strict_types=1);

namespace Core\Bootstrap;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\MySqlConnection;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Support\Container;
use Throwable;
use Workerman\Timer;
use Workerman\Worker;
use function class_exists;
use function config;
use function request;

class LaravelDb implements BootstrapInterface
{
    /**
     * Starts the Laravel database handling with the given worker.
     *
     * This method configures the database connections and other relevant settings
     * using the configuration provided in the application's configuration file.
     * It sets up the database connections, event dispatcher, and pagination settings.
     *
     * @param Worker|null $worker The worker instance, or null if not applicable.
     *
     * @return void
     */
    public static function start(?Worker $worker): void
    {
        if (!class_exists(Capsule::class)) {
            return;
        }

        $config = config('database', []);
        $connections = $config['connections'] ?? [];
        if (empty($connections)) {
            return;
        }

        $capsule = new Capsule(IlluminateContainer::getInstance());

        self::addConnections($capsule, $config, $connections);
        self::setEventDispatcher($capsule);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        if ($worker) {
            self::setupHeartbeat($capsule);
        }

        self::setupPaginator();
    }

    /**
     * Adds the database connections to the Capsule instance.
     *
     * @param Capsule $capsule The Capsule instance.
     * @param array $config The database configuration array.
     * @param array $connections The array of database connections.
     *
     * @return void
     */
    private static function addConnections(Capsule $capsule, array $config, array $connections): void
    {
        $default = $config['default'] ?? false;
        if ($default) {
            $defaultConfig = $connections[$default];
            $capsule->addConnection($defaultConfig);
        }

        foreach ($connections as $name => $connectionConfig) {
            $capsule->addConnection($connectionConfig, $name);
        }
    }

    /**
     * Sets the event dispatcher for the Capsule instance.
     *
     * @param Capsule $capsule The Capsule instance.
     *
     * @return void
     */
    private static function setEventDispatcher(Capsule $capsule): void
    {
        if (class_exists(Dispatcher::class) && !$capsule->getEventDispatcher()) {
            $capsule->setEventDispatcher(Container::make(Dispatcher::class, [IlluminateContainer::getInstance()]));
        }
    }

    /**
     * Sets up a heartbeat to keep MySQL connections alive.
     *
     * @param Capsule $capsule The Capsule instance.
     *
     * @return void
     */
    private static function setupHeartbeat(Capsule $capsule): void
    {
        Timer::add(55, function () use ($capsule) {
            foreach ($capsule->getDatabaseManager()->getConnections() as $connection) {
                /* @var MySqlConnection $connection */
                if ($connection->getConfig('driver') === 'mysql') {
                    try {
                        $connection->select('select 1');
                    } catch (Throwable $e) {
                        // TODO Log the exception or handle it appropriately
                    }
                }
            }
        });
    }

    /**
     * Sets up the Paginator and CursorPaginator configurations.
     *
     * @return void
     */
    private static function setupPaginator(): void
    {
        if (class_exists(Paginator::class)) {
            $request = request();

            if (method_exists(Paginator::class, 'queryStringResolver')) {
                Paginator::queryStringResolver(fn() => $request?->queryString());
            }

            Paginator::currentPathResolver(fn() => $request ? $request->path() : '/');

            Paginator::currentPageResolver(function ($pageName = 'page') use ($request) {
                if (!$request) {
                    return 1;
                }
                $page = (int)($request->input($pageName, 1));
                return $page > 0 ? $page : 1;
            });

            if (class_exists(CursorPaginator::class)) {
                CursorPaginator::currentCursorResolver(fn($cursorName = 'cursor') => Cursor::fromEncoded($request?->input($cursorName)));
            }
        }
    }
}
