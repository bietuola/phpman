<?php

namespace Core\Middleware;

use Support\Request;
use Support\Response;

/**
 * Interface MiddlewareInterface
 * Defines the contract that middleware classes must implement.
 * @package Core\Middleware
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param Request $request The HTTP request object.
     * @param callable $handler The next request handler in the pipeline.
     * @return Response The HTTP response object.
     */
    public function process(Request $request, callable $handler): Response;
}
