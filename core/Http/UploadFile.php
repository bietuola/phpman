<?php
declare(strict_types=1);

namespace Core\Http;

use Core\File;
use function pathinfo;

/**
 * Class UploadFile
 *
 * Represents an uploaded file with additional metadata and methods for handling file uploads.
 *
 * @package Core\Http
 */
class UploadFile extends File
{
    /**
     * @var string|null The original name of the uploaded file.
     */
    protected ?string $uploadName = null;

    /**
     * @var string|null The MIME type of the uploaded file.
     */
    protected ?string $uploadMimeType = null;

    /**
     * @var int|null The error code associated with the uploaded file, if any.
     */
    protected ?int $uploadErrorCode = null;

    /**
     * UploadFile constructor.
     *
     * Initializes an UploadFile instance with file metadata and calls the parent constructor.
     *
     * @param string $fileName The path to the uploaded file.
     * @param string $uploadName The original name of the uploaded file.
     * @param string $uploadMimeType The MIME type of the uploaded file.
     * @param int $uploadErrorCode The error code associated with the uploaded file, if any.
     */
    public function __construct(string $fileName, string $uploadName, string $uploadMimeType, int $uploadErrorCode)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
        parent::__construct($fileName);
    }

    /**
     * Retrieves the original name of the uploaded file.
     *
     * @return string|null The original name of the uploaded file.
     */
    public function getUploadName(): ?string
    {
        return $this->uploadName;
    }

    /**
     * Retrieves the MIME type of the uploaded file.
     *
     * @return string|null The MIME type of the uploaded file.
     */
    public function getUploadMimeType(): ?string
    {
        return $this->uploadMimeType;
    }

    /**
     * Retrieves the file extension of the uploaded file.
     *
     * @return string The file extension.
     */
    public function getUploadExtension(): string
    {
        return pathinfo($this->uploadName, PATHINFO_EXTENSION);
    }

    /**
     * Retrieves the error code associated with the uploaded file.
     *
     * @return int|null The error code, or null if no error occurred.
     */
    public function getUploadErrorCode(): ?int
    {
        return $this->uploadErrorCode;
    }

    /**
     * Checks if the uploaded file is valid (no error occurred during upload).
     *
     * @return bool True if the file is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return $this->uploadErrorCode === UPLOAD_ERR_OK;
    }

    /**
     * Retrieves the MIME type of the uploaded file (deprecated).
     *
     * @return string|null The MIME type of the uploaded file.
     * @deprecated Use getUploadMimeType() instead.
     */
    public function getUploadMineType(): ?string
    {
        return $this->uploadMimeType;
    }
}
