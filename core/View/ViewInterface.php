<?php

namespace Core\View;

interface ViewInterface
{
    /**
     * Render
     *
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null): string;
}