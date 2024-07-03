<?php
declare(strict_types=1);

namespace Core\Http;

use Core\RouteObject;
use function current;
use function filter_var;
use function ip2long;
use function is_array;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

/**
 * Class Request
 *
 * Represents an HTTP request with additional functionality for handling input, headers, and IP addresses.
 *
 * @property array $_view_vars
 * @package Core\Http
 */
class Request extends \Workerman\Protocols\Http\Request
{
    /**
     * @var string|null The name of the plugin.
     */
    public ?string $plugin = null;

    /**
     * @var string|null The name of the application.
     */
    public ?string $app = null;

    /**
     * @var string|null The name of the controller.
     */
    public ?string $controller = null;

    /**
     * @var string|null The name of the action.
     */
    public ?string $action = null;

    /**
     * @var RouteObject|null The matched route object.
     */
    public ?RouteObject $route = null;

    /**
     * Retrieves all input data (both POST and GET).
     *
     * @return mixed|null All input data.
     */
    public function all()
    {
        return $this->post() + $this->get();
    }

    /**
     * Retrieves input data by name, with a default value if not present.
     *
     * @param string $name The input parameter name.
     * @param mixed $default The default value if the parameter is not found.
     * @return mixed|null The value of the input parameter, or the default value if not found.
     */
    public function input(string $name, $default = null)
    {
        $post = $this->post();
        if (isset($post[$name])) {
            return $post[$name];
        }
        $get = $this->get();
        return $get[$name] ?? $default;
    }

    /**
     * Retrieves only specified keys from the input data.
     *
     * @param array $keys The keys to retrieve.
     * @return array The subset of input data containing only the specified keys.
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        $result = [];
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    /**
     * Retrieves input data excluding specified keys.
     *
     * @param array $keys The keys to exclude.
     * @return mixed|null The input data excluding the specified keys.
     */
    public function except(array $keys)
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * Retrieves uploaded file(s) by name or all uploaded files.
     *
     * @param string|null $name The name of the uploaded file(s).
     * @return null|UploadFile[]|UploadFile The parsed UploadFile object(s) or null if not found.
     */
    public function file($name = null)
    {
        $files = parent::file($name);
        if (null === $files) {
            return $name === null ? [] : null;
        }
        if ($name !== null) {
            // Single file or multiple files with the same name
            if (is_array(current($files))) {
                return $this->parseFiles($files);
            }
            return $this->parseFile($files);
        }
        $uploadFiles = [];
        foreach ($files as $name => $file) {
            // Multiple files with different names
            if (is_array(current($file))) {
                $uploadFiles[$name] = $this->parseFiles($file);
            } else {
                $uploadFiles[$name] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * Parses a single uploaded file array into an UploadFile object.
     *
     * @param array $file The array representing an uploaded file.
     * @return UploadFile The parsed UploadFile object.
     */
    protected function parseFile(array $file): UploadFile
    {
        return new UploadFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }

    /**
     * Parses multiple uploaded files arrays into UploadFile objects.
     *
     * @param array $files The arrays representing multiple uploaded files.
     * @return array An array of parsed UploadFile objects.
     */
    protected function parseFiles(array $files): array
    {
        $uploadFiles = [];
        foreach ($files as $key => $file) {
            if (is_array(current($file))) {
                $uploadFiles[$key] = $this->parseFiles($file);
            } else {
                $uploadFiles[$key] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * Retrieves the remote IP address of the client.
     *
     * @return string The remote IP address.
     */
    public function getRemoteIp(): string
    {
        return $this->connection->getRemoteIp();
    }

    /**
     * Retrieves the remote port number of the client.
     *
     * @return int The remote port number.
     */
    public function getRemotePort(): int
    {
        return $this->connection->getRemotePort();
    }

    /**
     * Retrieves the local IP address the server is bound to.
     *
     * @return string The local IP address.
     */
    public function getLocalIp(): string
    {
        return $this->connection->getLocalIp();
    }

    /**
     * Retrieves the local port number the server is bound to.
     *
     * @return int The local port number.
     */
    public function getLocalPort(): int
    {
        return $this->connection->getLocalPort();
    }

    /**
     * Retrieves the real IP address of the client, considering proxies and safe mode.
     *
     * @param bool $safeMode Whether to use safe mode to prevent spoofing.
     * @return string The real IP address of the client.
     */
    public function getRealIp(bool $safeMode = true): string
    {
        $remoteIp = $this->getRemoteIp();
        if ($safeMode && !static::isIntranetIp($remoteIp)) {
            return $remoteIp;
        }
        $ip = $this->header('x-real-ip', $this->header('x-forwarded-for', $this->header('client-ip', $this->header('x-client-ip', $this->header('via', $remoteIp)))));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $remoteIp;
    }

    /**
     * Retrieves the full URL of the request, including scheme, host, and path.
     *
     * @return string The full URL of the request.
     */
    public function fullUrl(): string
    {
        return '//' . $this->host() . $this->uri();
    }

    /**
     * Checks if the request is an AJAX request.
     *
     * @return bool True if the request is AJAX, false otherwise.
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Checks if the request is a PJAX request.
     *
     * @return bool True if the request is PJAX, false otherwise.
     */
    public function isPjax(): bool
    {
        return (bool)$this->header('X-PJAX');
    }

    /**
     * Checks if the client expects a JSON response.
     *
     * @return bool True if the client expects JSON, false otherwise.
     */
    public function expectsJson(): bool
    {
        return ($this->isAjax() && !$this->isPjax()) || $this->acceptJson();
    }

    /**
     * Checks if the client accepts JSON responses.
     *
     * @return bool True if the client accepts JSON, false otherwise.
     */
    public function acceptJson(): bool
    {
        return str_contains($this->header('accept', ''), 'json');
    }

    /**
     * Checks if an IP address is within an intranet range.
     *
     * @param string $ip The IP address to check.
     * @return bool True if the IP is within an intranet range, false otherwise.
     */
    public static function isIntranetIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        $reservedIps = [
            1681915904 => 1686110207, // 100.64.0.0 -  100.127.255.255
            3221225472 => 3221225727, // 192.0.0.0 - 192.0.0.255
            3221225984 => 3221226239, // 192.0.2.0 - 192.0.2.255
            3227017984 => 3227018239, // 192.88.99.0 - 192.88.99.255
            3323068416 => 3323199487, // 198.18.0.0 - 198.19.255.255
            3325256704 => 3325256959, // 198.51.100.0 - 198.51.100.255
            3405803776 => 3405804031, // 203.0.113.0 - 203.0.113.255
            3758096384 => 4026531839, // 224.0.0.0 - 239.255.255.255
        ];
        $ipLong = ip2long($ip);
        foreach ($reservedIps as $ipStart => $ipEnd) {
            if (($ipLong >= $ipStart) && ($ipLong <= $ipEnd)) {
                return true;
            }
        }
        return false;
    }
}
