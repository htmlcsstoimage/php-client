<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

use HtmlCssToImage\Recording\RecordingError;
use HtmlCssToImage\Recording\RecordingStatus;

/** Lifecycle information from HTTP 200 or 202. Timestamps are ISO 8601 UTC strings. @api */
final readonly class RecordingStatusSuccessResponse
{
    public true $success;

    public function __construct(
        public string $id,
        public string $recordingUrl,
        public RecordingStatus $status,
        public string $statusEnteredAt,
        public int $attemptCount,
        public ?int $storedDurationMs = null,
        public ?bool $storageDestinationHctiStorageDisabled = null,
        public ?RecordingError $error = null,
    ) {
        $this->success = true;
    }
}
