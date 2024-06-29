<?php
declare(strict_types=1);

namespace Support;

use function config;
use function request;

/**
 * Class View
 *
 * @package Support
 */
class View
{
    /**
     * Assign.
     *
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    public static function assign($name, $value = null)
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $handler = config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
        $handler::assign($name, $value);
    }
}