<?php

namespace Core\Middleware;

use Support\Request;
use Support\Response;

/**
 * Class StaticFile
 *
 * Middleware to handle static file requests.
 *
 * @package Core\Middleware
 */
class StaticFile implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Checks if the requested path contains '/.' which is typically used
     * to access files beginning with dot (hidden files). If found, returns
     * a 403 Forbidden response. Otherwise, adds cross-domain headers to the
     * response returned by the next handler in the pipeline.
     *
     * @param Request $request The HTTP request object.
     * @param callable $handler The next request handler in the pipeline.
     * @return Response The HTTP response object.
     */
    public function process(Request $request, callable $handler): Response
    {
        // Prohibit access to files beginning with dot (hidden files)
        if (str_contains($request->path(), '/.')) {
            return response('<h1>403 Forbidden</h1>', 403);
        }

        // Call the next handler in the middleware pipeline
        $response = $handler($request);

        // Add cross-domain HTTP headers
        $response->withHeaders([
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        return $response;
    }
}
