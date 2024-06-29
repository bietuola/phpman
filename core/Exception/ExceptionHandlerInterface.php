<?php

namespace Core\Exception;

use support\Request;
use support\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void;

    /**
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response;
}