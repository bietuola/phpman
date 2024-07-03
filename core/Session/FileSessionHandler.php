<?php
declare(strict_types=1);

namespace Core\Session;

use Workerman\Protocols\Http\Session\FileSessionHandler as FileHandler;

/**
 * FileSessionHandler extends FileHandler
 *
 * Custom session handler using files for session storage.
 *
 * This class extends the FileHandler provided by Workerman,
 * allowing for customization or additional functionality if needed
 * in the future.
 *
 * @package Core\Session
 */
class FileSessionHandler extends FileHandler
{
    // You can add custom session handling logic or configuration here if needed
}
