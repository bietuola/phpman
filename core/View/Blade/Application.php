<?php

namespace Core\View\Blade;

use Illuminate\Container\Container;

/**
 * Class Application
 *
 * Represents the application container for Blade views.
 * Extends Illuminate\Container\Container for dependency injection and service management.
 *
 * @package Core\View\Blade
 */
class Application extends Container
{
    /**
     * Get the namespace used by the application.
     *
     * @return string The namespace used by the application.
     */
    public function getNamespace(): string
    {
        return 'app\\'; // Default namespace 'app\\' for the application
    }
}
