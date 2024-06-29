<?php

namespace Core\Bootstrap;

use Workerman\Worker;

interface BootstrapInterface
{
    /**
     * onWorkerStart
     *
     * @param Worker|null $worker
     * @return mixed
     */
    public static function start(?Worker $worker);
}