<?php
declare(strict_types=1);

namespace Core\Session;

use Workerman\Protocols\Http\Session\RedisSessionHandler as RedisHandler;

/**
 * RedisSessionHandler extends RedisHandler
 *
 * Custom session handler using Redis for session storage.
 *
 * This class extends the RedisHandler provided by Workerman,
 * allowing for customization or additional functionality if needed
 * in the future.
 *
 * @package Core\Session
 */
class RedisSessionHandler extends RedisHandler
{
    // You can add custom session handling logic or configuration here if needed
}
