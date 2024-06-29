<?php
declare(strict_types=1);

global $argv;

return [
    // 文件更新检测和自动重载
    'monitor' => [
        'handler'     => Process\Monitor::class, // 指定用于监控的类（Process\Monitor）
        'reloadable'  => false, // 设置为 false，表示文件更改时不会自动重载。
        'constructor' => [
            // 监控这些目录
            'monitorDir'        => array_merge([
                app_path(),
                admin_path(),
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env'
            ],
                // 用于匹配特定模式的所有文件（例如，base_path() . '/plugin/*/app'），并返回文件路径数组。这里用于包含 plugin 目录下所有的 app、config 和 api 子目录。
                glob(base_path() . '/plugin/*/app'),
                glob(base_path() . '/plugin/*/config'),
                glob(base_path() . '/plugin/*/api')
            ),
            // 监控具有这些后缀的文件
            'monitorExtensions' => [
                'php', 'html', 'htm', 'env'
            ],
            'options'           => [
                'enable_file_monitor'   => !in_array('-d', $argv) && DIRECTORY_SEPARATOR === '/',
                'enable_memory_monitor' => DIRECTORY_SEPARATOR === '/',
            ]
        ]
    ]
];
