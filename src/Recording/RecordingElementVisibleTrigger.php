<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Wait for a matching element to become visible before starting recording. Fails if the timeout expires.
 * @see https://docs.htmlcsstoimage.com/parameters/trigger/
 * @api
 */
final readonly class RecordingElementVisibleTrigger extends RecordingTrigger
{
    /**
     * @param string $selector CSS selector for the element to wait for.
     * @param int|null $timeoutMs Maximum wait in milliseconds. Defaults to 1000; minimum 100. Plan
     *     limits apply.
     */
    public function __construct(
        public string $selector,
        public ?int $timeoutMs = null,
    ) {
        parent::__construct('element_visible');
    }
}
