<?php
declare(strict_types=1);

namespace Core\Exception;

use RuntimeException;

/**
 * Class FileException
 *
 * This class represents exceptions that occur during file operations.
 * It extends the RuntimeException class and can be used to handle errors
 * such as file not found, read/write failures, and permission issues.
 */
class FileException extends RuntimeException
{
    // You can add custom methods or properties here if needed
}
