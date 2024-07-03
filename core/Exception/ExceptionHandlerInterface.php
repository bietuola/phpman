<?php

namespace Core\Exception;

use Support\Request;
use Support\Response;
use Throwable;

/**
 * Interface ExceptionHandlerInterface
 *
 * This interface defines the contract for exception handler classes
 * that manage the reporting and rendering of exceptions.
 *
 * Implementations of this interface should provide the logic to report
 * exceptions (e.g., log them) and render appropriate responses to the
 * client when an exception occurs.
 */
interface ExceptionHandlerInterface
{
    /**
     * Reports an exception.
     *
     * This method should contain the logic to log or otherwise report
     * the given exception. It is typically used to send exception details
     * to a logging service or save them in a log file.
     *
     * @param Throwable $exception The exception to report.
     *                             This parameter provides the details of the
     *                             exception that occurred, including the message,
     *                             code, file, and line number.
     *
     * @return void This method does not return any value.
     */
    public function report(Throwable $exception): void;

    /**
     * Renders an exception into an HTTP response.
     *
     * This method should generate an appropriate HTTP response based on the
     * exception details and the request. It determines the format of the response
     * (e.g., JSON or HTML) and includes relevant exception information.
     *
     * @param Request $request The request instance.
     *                         This object provides details about the client's request,
     *                         such as headers, method, and URL.
     *
     * @param Throwable $exception The exception to render.
     *                             This parameter provides the details of the exception
     *                             that occurred, including the message, code, file, and
     *                             line number.
     *
     * @return Response The response object that will be sent to the client.
     *                  This object contains the HTTP status code, headers, and body
     *                  content, which can vary depending on whether the request expects
     *                  JSON or HTML.
     */
    public function render(Request $request, Throwable $exception): Response;
}
