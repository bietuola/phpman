<?php
/**
 * This file is part of phpman.
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    tony<tony@phpman.com>
 * @copyright tony<tony@phpman.com>
 * @link      http://www.phpman.com/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);

namespace Core\Bootstrap;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\MySqlConnection;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use support\Container;
use Throwable;
use Workerman\Timer;
use Workerman\Worker;
use function class_exists;
use function config;

class LaravelDb implements BootstrapInterface
{
    /**
     * @param Worker|null $worker
     * @return void
     */
    public static function start(?Worker $worker): void
    {
        if (!class_exists(Capsule::class)) {
            return;
        }

        $config = config('database', []);
        $connections = $config['connections'] ?? [];
        if (!$connections) {
            return;
        }

        $capsule = new Capsule(IlluminateContainer::getInstance());

        $default = $config['default'] ?? false;
        if ($default) {
            $defaultConfig = $connections[$config['default']];
            $capsule->addConnection($defaultConfig);
        }

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        if (class_exists(Dispatcher::class) && !$capsule->getEventDispatcher()) {
            $capsule->setEventDispatcher(Container::make(Dispatcher::class, [IlluminateContainer::getInstance()]));
        }

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        // Heartbeat
        if ($worker) {
            Timer::add(55, function () use ($default, $connections, $capsule) {
                foreach ($capsule->getDatabaseManager()->getConnections() as $connection) {
                    /* @var MySqlConnection $connection * */
                    if ($connection->getConfig('driver') == 'mysql') {
                        try {
                            $connection->select('select 1');
                        } catch (Throwable $e) {
                        }
                    }
                }
            });
        }

        // Paginator
        if (class_exists(Paginator::class)) {
            if (method_exists(Paginator::class, 'queryStringResolver')) {
                Paginator::queryStringResolver(function () {
                    $request = request();
                    return $request?->queryString();
                });
            }
            Paginator::currentPathResolver(function () {
                $request = request();
                return $request ? $request->path() : '/';
            });
            Paginator::currentPageResolver(function ($pageName = 'page') {
                $request = request();
                if (!$request) {
                    return 1;
                }
                $page = (int)($request->input($pageName, 1));
                return $page > 0 ? $page : 1;
            });
            if (class_exists(CursorPaginator::class)) {
                CursorPaginator::currentCursorResolver(function ($cursorName = 'cursor') {
                    return Cursor::fromEncoded(request()->input($cursorName));
                });
            }
        }
    }
}