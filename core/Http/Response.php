<?php
declare(strict_types=1);

namespace Core\Http;

use Throwable;
use Core\App;
use function filemtime;
use function gmdate;

/**
 * Class Response
 *
 * Represents an HTTP response object with additional functionality for file handling and exceptions.
 *
 * @package Core\Http
 */
class Response extends \Workerman\Protocols\Http\Response
{
    /**
     * @var Throwable|null The exception associated with the response, if any.
     */
    protected ?Throwable $exception = null;

    /**
     * Sets the response to serve the specified file.
     *
     * @param string $file The path to the file to serve.
     * @return $this
     */
    public function file(string $file): Response
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }
        return $this->withFile($file);
    }

    /**
     * Sets the response for downloading the specified file.
     *
     * @param string $file The path to the file to download.
     * @param string $downloadName The optional name for the downloaded file.
     * @return $this
     */
    public function download(string $file, string $downloadName = ''): Response
    {
        $this->withFile($file);
        if ($downloadName) {
            $this->header('Content-Disposition', "attachment; filename=\"$downloadName\"");
        }
        return $this;
    }

    /**
     * Checks if the file has been modified since the If-Modified-Since header.
     *
     * @param string $file The path to the file to check.
     * @return bool Whether the file has not been modified since the If-Modified-Since header.
     */
    protected function notModifiedSince(string $file): bool
    {
        $ifModifiedSince = App::request()->header('if-modified-since');
        if ($ifModifiedSince === null || !is_file($file) || !($mtime = filemtime($file))) {
            return false;
        }
        return $ifModifiedSince === gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * Gets or sets the exception associated with the response.
     *
     * @param Throwable|null $exception The exception to set, or null to get the current exception.
     * @return Throwable|null The current exception associated with the response, or null if none set.
     */
    public function exception(Throwable $exception = null): ?Throwable
    {
        if ($exception) {
            $this->exception = $exception;
        }
        return $this->exception;
    }
}
