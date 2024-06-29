<?php

namespace Core\View\Blade;

use Illuminate\Container\Container;

class Application extends Container
{
    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return 'app\\';
    }
}