<?php
declare(strict_types=1);

use Core\App;
use Core\Config;
use Core\Route;
use Support\Blade;
use Support\Container;
use Support\Raw;
use Support\Request;
use Support\Response;
use Support\Translation;
use Workerman\Protocols\Http\Session;
use Workerman\Worker;

// Project base path
define('BASE_PATH', dirname(__DIR__));

/**
 * return the program execute directory
 *
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = is_phar() ? dirname(Phar::running(false)) : BASE_PATH;
    }
    return path_combine($runPath, $path);
}

/**
 * 如果参数 $path 等于 false，将返回程序当前执行目录
 *
 * @param false|string $path 参数可以是字符串或布尔值 false
 * @return string 返回程序基本路径或当前执行目录
 */
function base_path(false|string $path = ''): string
{
    // 如果 $path 为 false，则返回程序当前执行目录
    if (false === $path) {
        return run_path();
    }
    // 否则，返回 BASE_PATH 与 $path 组合后的路径
    return path_combine(BASE_PATH, $path);
}

/**
 * 返回后台目录路径
 * @param string $path
 * @return string
 */
function admin_path(string $path = ''): string
{
    // 将 BASE_PATH 与 'admin' 目录组合，然后再与传入的 $path 组合
    return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'admin', $path);
}

/**
 * 返回应用程序目录下的某个路径
 *
 * @param string $path 要组合的路径，默认为空字符串
 * @return string 返回组合后的完整路径
 */
function app_path(string $path = ''): string
{
    // 将 BASE_PATH 与 'app' 目录组合，然后再与传入的 $path 组合
    return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'app', $path);
}

/**
 * Public path
 *
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
    static $publicPath = '';
    if (!$publicPath) {
        $publicPath = config('app.public_path') ?: run_path('public');
    }
    return path_combine($publicPath, $path);
}

/**
 * Config path
 *
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'config', $path);
}

/**
 * Runtime path
 *
 * @param string $path
 * @return string
 */
function runtime_path(string $path = ''): string
{
    static $runtimePath = '';
    if (!$runtimePath) {
        $runtimePath = \config('app.runtime_path') ?: run_path('runtime');
    }
    return path_combine($runtimePath, $path);
}

/**
 * Generate paths based on given information
 *
 * @param string $front
 * @param string $back
 * @return string
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * Response
 *
 * @param int $status
 * @param array $headers
 * @param string $body
 * @return Response
 */
function response(string $body = '', int $status = 200, array $headers = []): Response
{
    return new Response($status, $headers, $body);
}

/**
 * Json response
 *
 * @param $data
 * @param int $options
 * @return Response
 */
function json($data, int $options = JSON_UNESCAPED_UNICODE): Response
{
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
}

/**
 * Xml response
 *
 * @param $xml
 * @return Response
 */
function xml($xml): Response
{
    if ($xml instanceof SimpleXMLElement) {
        $xml = $xml->asXML();
    }
    return new Response(200, ['Content-Type' => 'text/xml'], $xml);
}

/**
 * Jsonp response
 *
 * @param $data
 * @param string $callbackName
 * @return Response
 */
function jsonp($data, string $callbackName = 'callback'): Response
{
    if (!is_scalar($data) && null !== $data) {
        $data = json_encode($data);
    }
    return new Response(200, [], "$callbackName($data)");
}

/**
 * Redirect response
 *
 * @param string $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect(string $location, int $status = 302, array $headers = []): Response
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * View response
 *
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @param string|null $plugin
 * @return Response
 */
function view(string $template, array $vars = [], string $app = null, string $plugin = null): Response
{
    $request = \request();
    $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
    $handler = \config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
    return new Response(200, [], $handler::render($template, $vars, $app, $plugin));
}

/**
 * Raw view response
 *
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 * @throws Throwable
 */
function raw_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Raw::render($template, $vars, $app));
}

/**
 * Blade view response
 *
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function blade_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Blade::render($template, $vars, $app));
}

/**
 * Get request
 * @return Request
 */
function request(): Request
{
    return App::request();
}

/**
 * Get config
 *
 * @param string|null $key
 * @param $default
 * @return array|mixed|null
 */
function config(string $key = null, $default = null): mixed
{
    return Config::get($key, $default);
}

/**
 * Create url
 *
 * @param string $name
 * @param ...$parameters
 * @return string
 */
function route(string $name, ...$parameters): string
{
    $route = Route::getByName($name);
    if (!$route) {
        return '';
    }

    if (!$parameters) {
        return $route->url();
    }

    if (is_array(current($parameters))) {
        $parameters = current($parameters);
    }

    return $route->url($parameters);
}

/**
 * Session
 *
 * @param mixed|null $key
 * @param mixed|null $default
 * @return mixed|bool|Session
 * @throws Exception
 */
function session(mixed $key = null, mixed $default = null): mixed
{
    $session = \request()->session();
    if (null === $key) {
        return $session;
    }
    if (is_array($key)) {
        $session->put($key);
        return null;
    }
    if (strpos($key, '.')) {
        $keyArray = explode('.', $key);
        $value = $session->all();
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
    return $session->get($key, $default);
}

/**
 * Translation
 *
 * @param string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
{
    $res = Translation::trans($id, $parameters, $domain, $locale);
    return $res === '' ? $id : $res;
}

/**
 * Locale
 *
 * @param string|null $locale
 * @return string
 */
function locale(string $locale = null): string
{
    if (!$locale) {
        return Translation::getLocale();
    }
    Translation::setLocale($locale);
    return $locale;
}

/**
 * 404 not found
 *
 * @return Response
 */
function not_found(): Response
{
    return new Response(404, [], file_get_contents(public_path() . '/404.html'));
}

/**
 * Copy dir
 *
 * @param string $source
 * @param string $dest
 * @param bool $overwrite
 * @return void
 */
function copy_dir(string $source, string $dest, bool $overwrite = false)
{
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                copy_dir("$source/$file", "$dest/$file", $overwrite);
            }
        }
    } else {
        if (file_exists($source) && ($overwrite || !file_exists($dest))) {
            copy($source, $dest);
        }
    }
}

/**
 * Remove dir
 *
 * @param string $dir
 * @return bool
 */
function remove_dir(string $dir): bool
{
    if (is_link($dir) || is_file($dir)) {
        return unlink($dir);
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file") && !is_link($dir)) ? remove_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * Bind worker
 *
 * @param $worker
 * @param $class
 */
function worker_bind($worker, $class)
{
    $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect',
        'onWorkerReload'
    ];
    foreach ($callbackMap as $name) {
        if (method_exists($class, $name)) {
            $worker->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onWorkerStart')) {
        call_user_func([$class, 'onWorkerStart'], $worker);
    }
}

/**
 * Start worker
 *
 * @param $processName
 * @param $config
 * @return void
 */
function worker_start($processName, $config): void
{
    $worker = new Worker($config['listen'] ?? null, $config['context'] ?? []);
    $propertyMap = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
        'protocol',
    ];
    $worker->name = $processName;
    foreach ($propertyMap as $property) {
        if (isset($config[$property])) {
            $worker->$property = $config[$property];
        }
    }
    /**
     * @param $worker
     * @return void
     */
    $worker->onWorkerStart = function ($worker) use ($config) {
        require_once base_path('/support/bootstrap.php');
        if (isset($config['handler'])) {
            if (!class_exists($config['handler'])) {
                echo "process error: class {$config['handler']} not exists\n";
                return;
            }
            $instance = Container::make($config['handler'], $config['constructor'] ?? []);
            worker_bind($worker, $instance);
        }
    };
}

/**
 * Get realpath
 *
 * @param string $filePath
 * @return string
 */
function get_realpath(string $filePath): string
{
    if (str_starts_with($filePath, 'phar://')) {
        return $filePath;
    } else {
        return realpath($filePath);
    }
}

/**
 * Is phar
 *
 * @return bool
 */
function is_phar(): bool
{
    return class_exists(Phar::class, false) && Phar::running();
}

/**
 * Get cpu count
 *
 * @return int
 */
function cpu_count(): int
{
    // Windows does not support the number of processes setting.
    if (DIRECTORY_SEPARATOR === '\\') {
        return 1;
    }
    $count = 4;
    if (is_callable('shell_exec')) {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = (int)shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)shell_exec('nproc');
        }
    }
    return $count > 0 ? $count : 4;
}

/**
 * Get request parameters, if no parameter name is passed, an array of all values is returned, default values is supported
 *
 * @param string|null $param param's name
 * @param mixed|null $default default value
 * @return mixed|null
 */
function input(string $param = null, $default = null)
{
    return is_null($param) ? request()->all() : request()->input($param, $default);
}
