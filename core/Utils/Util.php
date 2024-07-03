<?php
declare(strict_types=1);

namespace Core\Utils;

/**
 * Class Util
 *
 * A utility class providing various helper functions.
 *
 * @package Core\Utils
 */
class Util
{
    /**
     * Scan a directory and optionally prepend the base path to each entry.
     *
     * @param string $basePath The base path of the directory to scan.
     * @param bool $withBasePath Whether to prepend the base path to each entry.
     * @return array An array of directory entries with or without the base path prepended.
     */
    public static function scanDir(string $basePath, bool $withBasePath = true): array
    {
        if (!is_dir($basePath)) {
            return [];
        }

        // Scan the directory and exclude '.' and '..' entries.
        $paths = array_diff(scandir($basePath), ['.', '..']) ?: [];

        // Optionally prepend the base path to each entry.
        return $withBasePath ? array_map(static function ($path) use ($basePath) {
            return $basePath . DIRECTORY_SEPARATOR . $path;
        }, $paths) : $paths;
    }
}
