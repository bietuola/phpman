<?php
declare(strict_types=1);

use Core\Utils\Env;

return [
    'listen'           => Env::get('SERVER.LISTEN', ''),
    'transport'        => 'tcp',
    'context'          => [],
    'name'             => 'phpman',
    'count'            => 1, // cpu_count() * 4
    'user'             => '',
    'group'            => '',
    'reusePort'        => false,
    'event_loop'       => '',
    'stop_timeout'     => 2,
    'pid_file'         => runtime_path() . '/phpman.pid',
    'status_file'      => runtime_path() . '/phpman.status',
    'stdout_file'      => runtime_path() . '/logs/stdout.log',
    'log_file'         => runtime_path() . '/logs/workerman.log',
    'max_package_size' => 10 * 1024 * 1024
];
