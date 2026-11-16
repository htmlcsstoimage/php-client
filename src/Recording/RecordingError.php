<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** An attempt error. Use the recording status to distinguish retries from terminal failure. @api */
final readonly class RecordingError
{
    /**
     * @param string $code Machine-readable attempt error code.
     * @param string $message Human-readable failure description.
     * @param string $occurredAt UTC timestamp as an ISO 8601 string.
     * @param bool $retryable Whether this kind of error is eligible for retry.
     * @param string|null $actionPath Failing action path, or null when unrelated to an action.
     */
    public function __construct(
        public string $code,
        public string $message,
        public string $occurredAt,
        public bool $retryable,
        public ?string $actionPath = null,
    ) {
    }
}
