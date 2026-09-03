<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/**
 * Unsuccessful API response.
 *
 * Network and timeout failures are raised by the configured PSR-18 client and
 * do not produce this response type.
 *
 * @api
 */
final readonly class ApiErrorResponse
{
    /** Always false for this response type. */
    public false $success;

    /**
     * @param string $error Short API error name.
     * @param int $statusCode HTTP status code returned by the API.
     * @param string|null $message Human-readable error details.
     * @param list<ValidationError>|null $validationErrors Field errors.
     */
    public function __construct(
        public string $error,
        public int $statusCode,
        public ?string $message = null,
        public ?array $validationErrors = null,
    ) {
        $this->success = false;
    }
}
