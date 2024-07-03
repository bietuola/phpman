<?php
declare(strict_types=1);

namespace Core\Exception;

use Psr\Log\LoggerInterface;
use Support\Request;
use Support\Response;
use Throwable;
use function json_encode;
use function nl2br;
use function trim;

/**
 * Class ExceptionHandler
 *
 * This class handles exceptions thrown within the application. It reports
 * exceptions to the logger and renders appropriate responses based on the
 * request type (e.g., JSON or HTML).
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var bool
     */
    protected bool $debug;

    /**
     * @var array
     */
    protected array $dontReport = [];

    /**
     * ExceptionHandler constructor.
     *
     * @param LoggerInterface $logger The logger instance used for reporting exceptions.
     * @param bool $debug Flag indicating whether to show detailed error messages.
     */
    public function __construct(LoggerInterface $logger, bool $debug)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * Reports an exception.
     *
     * This method logs the exception if it should be reported. It includes
     * details such as the client's IP address, request method, and full URL.
     *
     * @param Throwable $exception The exception to report.
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $logs = '';
        if ($request = request()) {
            $logs = sprintf(
                '%s %s %s',
                $request->getRealIp(),
                $request->method(),
                trim($request->fullUrl(), '/')
            );
        }

        $this->logger->error($logs . PHP_EOL . $exception);
    }

    /**
     * Renders an exception into an HTTP response.
     *
     * This method generates an appropriate response based on the exception
     * details and the request type (JSON or HTML).
     *
     * @param Request $request The request instance.
     * @param Throwable $exception The exception to render.
     * @return Response The generated response.
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $code = $exception->getCode() ?: 500;

        if ($request->expectsJson()) {
            $json = [
                'code' => $code,
                'msg'  => $this->debug ? $exception->getMessage() : 'Server internal error',
            ];

            if ($this->debug) {
                $json['traces'] = (string)$exception;
            }

            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $error = $this->debug ? nl2br((string)$exception) : 'Server internal error';
        return new Response(500, [], $error);
    }

    /**
     * Determines if the exception should not be reported.
     *
     * This method checks the exception against a list of types that should not
     * be reported.
     *
     * @param Throwable $e The exception to check.
     * @return bool True if the exception should not be reported, false otherwise.
     */
    protected function shouldntReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Magic method to access protected properties.
     *
     * This method provides compatibility for accessing the `_debug` property.
     *
     * @param string $name The name of the property.
     * @return bool|null The value of the property, or null if not found.
     */
    public function __get(string $name)
    {
        return $name === '_debug' ? $this->debug : null;
    }
}
