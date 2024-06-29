<?php
declare(strict_types=1);

namespace Core\Session;

use Workerman\Protocols\Http\Session\RedisClusterSessionHandler as RedisClusterHandler;

/**
 * Class FileSessionHandler
 *
 * @package Core\Session
 */
class RedisClusterSessionHandler extends RedisClusterHandler
{

}