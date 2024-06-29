<?php
declare(strict_types=1);

namespace Support;

use Core\View\ViewInterface;
use Throwable;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function extract;
use function is_array;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function request;

/**
 * Class Raw
 *
 * @package Support\View
 */
class Raw implements ViewInterface
{
    /**
     * Assign.
     *
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        $request = request();
        $request->_view_vars = array_merge((array)$request->_view_vars, is_array($name) ? $name : [$name => $value]);
    }

    /**
     * Render
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @param string|null $plugin
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $app = $app === null ? $request->app : $app;
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $__template_path__ = $app === '' ? "$baseViewPath/view/$template.$viewSuffix" : "$baseViewPath/$app/view/$template.$viewSuffix";

        extract((array)$request->_view_vars);
        extract($vars);
        ob_start();
        // Try to include php file.
        try {
            include $__template_path__;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }
}