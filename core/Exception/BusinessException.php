<?php
declare(strict_types=1);

namespace Core\Exception;

use Exception;
use Support\Request;
use Support\Response;
use function json_encode;

/**
 * Class BusinessException
 *
 * This class represents a custom exception used for business logic errors.
 * It extends the base Exception class and provides a method to render a
 * user-friendly response when the exception is thrown.
 *
 * The render method checks the request type and returns a JSON response if
 * the client expects JSON, otherwise, it returns a plain text response.
 */
class BusinessException extends Exception
{
    /**
     * Renders a response based on the exception details.
     *
     * This method generates a response that can be returned to the client
     * when this exception is thrown. If the client expects a JSON response,
     * it returns a JSON formatted response containing the error code and
     * message. Otherwise, it returns a plain text response with the error
     * message.
     *
     * @param Request $request The request instance that triggered the exception.
     *                         This object provides details about the client's request.
     *
     * @return Response|null The response object that will be sent to the client.
     *                       If the request expects JSON, a JSON response is returned.
     *                       Otherwise, a plain text response is returned.
     */
    public function render(Request $request): ?Response
    {
        // Check if the client expects a JSON response
        if ($request->expectsJson()) {
            // Get the exception code, default to 500 if not set
            $code = $this->getCode() ?: 500;
            // Create the JSON response body
            $json = [
                'code' => $code,
                'msg'  => $this->getMessage()
            ];
            // Return a JSON response with the appropriate headers and body
            return new Response(200, ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Return a plain text response if JSON is not expected
        return new Response(200, [], $this->getMessage());
    }
}
