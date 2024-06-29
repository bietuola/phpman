<?php

namespace Core\Middleware;

use support\Request;
use support\Response;

/**
 * Class StaticFile
 *
 * @package Core\Middleware
 */
class StaticFile implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // Access to files beginning with. Is prohibited
        if (str_contains($request->path(), '/.')) {
            return response('<h1>403 forbidden</h1>', 403);
        }
        /**
         * @var Response $response
         */
        $response = $handler($request);

        // Add cross domain HTTP header
        $response->withHeaders([
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        return $response;
    }
}
