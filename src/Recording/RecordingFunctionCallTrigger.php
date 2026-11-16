<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Wait for the page to call window.RecordingReady() before starting recording. Fails if the timeout expires.
 * @see https://docs.htmlcsstoimage.com/parameters/trigger/
 * @api
 */
final readonly class RecordingFunctionCallTrigger extends RecordingTrigger
{
    /**
     * @param int|null $timeoutMs Maximum wait in milliseconds. Defaults to 1000; minimum 100. Plan
     *     limits apply.
     */
    public function __construct(
        public ?int $timeoutMs = null,
    ) {
        parent::__construct('function_call');
    }
}
