<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/** Created or deduplicated recording. The recording URL can be embedded or requested directly and waits until ready. @api */
final readonly class CreateRecordingSuccessResponse
{
    public true $success;

    public function __construct(
        public string $id,
        public string $statusUrl,
        public string $recordingUrl,
    ) {
        $this->success = true;
    }
}
