<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Scroll to an element matching a CSS selector, with optional offsets.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class SelectorScrollTarget extends ScrollTarget
{
    /**
     * @param string $selector CSS selector for the destination element.
     * @param int|float|null $offsetX Horizontal offset from the element. Defaults to 0.
     * @param int|float|null $offsetY Vertical offset from the element. Defaults to 0.
     * @param list<RecordingAction>|null $ifNotFound Fallback when missing. Omitted, null, or empty skips
     *     this movement and continues to the next step.
     */
    public function __construct(
        public string $selector,
        public int|float|null $offsetX = null,
        public int|float|null $offsetY = null,
        public ?array $ifNotFound = null,
    ) {
        parent::__construct('selector');
    }
}
