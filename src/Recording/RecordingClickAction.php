<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Find and click an element, scrolling it into view when needed.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingClickAction extends RecordingAction
{
    /**
     * @param string $selector CSS selector of the element to click.
     * @param int|null $timeoutMs Maximum wait in milliseconds. Defaults to 0 for a single check.
     * @param list<RecordingAction>|null $ifNotFound Fallback when missing. Omitted, null, or empty
     *     continues; use an error action to fail.
     * @param RecordingMouseButton|null $button Mouse button. Defaults to left.
     * @param int|null $count Event click count, from 1 to 3. Defaults to 1; this does not repeat the
     *     action.
     * @param int|null $delayMs Delay between mouse down and up, in milliseconds. Defaults to 0; maximum
     *     5000.
     * @param int|float|null $offsetX CSS pixels from the element's top-left. Supplying either offset
     *     makes the other default to 0.
     * @param int|float|null $offsetY CSS pixels from the element's top-left. Omit both offsets to use
     *     the default clickable point.
     */
    public function __construct(
        public string $selector,
        public ?int $timeoutMs = null,
        public ?array $ifNotFound = null,
        public ?RecordingMouseButton $button = null,
        public ?int $count = null,
        public ?int $delayMs = null,
        public int|float|null $offsetX = null,
        public int|float|null $offsetY = null,
    ) {
        parent::__construct('click');
    }
}
