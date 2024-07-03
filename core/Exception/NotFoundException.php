<?php
declare(strict_types=1);

namespace Core\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * This class represents an exception that indicates a specific item was not found.
 * It extends the base Exception class and implements the NotFoundExceptionInterface,
 * indicating it can be used within dependency injection containers to signal that
 * a requested entry was not found.
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    // You can add custom methods or properties here if needed
}
