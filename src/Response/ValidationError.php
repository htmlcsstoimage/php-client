<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/**
 * A validation error associated with one request field.
 *
 * @api
 */
final readonly class ValidationError
{
    /**
     * @param string $path API field path that failed validation.
     * @param string $message Human-readable validation message.
     */
    public function __construct(
        public string $path,
        public string $message,
    ) {
    }
}
