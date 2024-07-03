<?php
declare(strict_types=1);

namespace Core\Session;

use Workerman\Protocols\Http\Session\RedisClusterSessionHandler as RedisClusterHandler;

/**
 * RedisClusterSessionHandler extends RedisClusterHandler
 *
 * Custom session handler using Redis cluster for session storage.
 *
 * This class extends the RedisClusterHandler provided by Workerman,
 * allowing for customization or additional functionality if needed
 * in the future.
 *
 * @package Core\Session
 */
class RedisClusterSessionHandler extends RedisClusterHandler
{
    // You can add custom session handling logic or configuration here if needed
}
