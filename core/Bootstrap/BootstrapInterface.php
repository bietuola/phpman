<?php

namespace Core\Bootstrap;

use Workerman\Worker;

/**
 * Interface BootstrapInterface
 *
 * This interface defines the contract for the bootstrap classes
 * that initialize various components or services when a Workerman
 * worker starts.
 *
 * Implementations of this interface should provide the logic to
 * initialize and configure the necessary components required by
 * the application, such as database connections, event dispatchers,
 * or any other services.
 */
interface BootstrapInterface
{
    /**
     * This method is called when a Workerman worker starts.
     *
     * Implementations of this method should contain the initialization
     * and configuration logic for various components or services
     * required by the application.
     *
     * @param Worker|null $worker The Workerman worker instance, or null if not applicable.
     *                            This parameter can be used to access worker-specific settings
     *                            and perform worker-specific initialization tasks.
     *
     * @return mixed The return type is implementation-dependent. It could return void, or it could
     *               return a status or result indicating the outcome of the initialization process.
     */
    public static function start(?Worker $worker);
}
