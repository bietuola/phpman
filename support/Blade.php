<?php
declare(strict_types=1);

namespace Support;

use Core\View\Blade\Blade as BladeView;
use Core\View\ViewInterface;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function is_array;
use function request;
use function runtime_path;

class Blade implements ViewInterface
{
    /**
     * Assign
     * @param $name
     * @param $value
     * @return void
     */
    public static function assign($name, $value = null): void
    {
        $request = request();
        $request->_view_vars = array_merge($request->_view_vars, is_array($name) ? $name : [$name => $value]);
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
        static $views = [];
        $request = request();
        $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
        $app = $app === null ? $request->app : $app;
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $key = "$plugin-$app";
        if (!isset($views[$key])) {
            $viewPath = $app === '' ? "$baseViewPath/view" : "$baseViewPath/$app/view";
            $views[$key] = new BladeView($viewPath, runtime_path() . '/views');
            $extension = config("{$configPrefix}view.extension");
            if ($extension) {
                $extension($views[$key]);
            }
        }
        $vars = array_merge((array)$request->_view_vars, $vars);
        return $views[$key]->render($template, $vars);
    }
}