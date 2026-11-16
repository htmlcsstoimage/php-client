<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** One timed scroll movement within a scroll action. Steps run in order.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class ScrollStep
{
    /**
     * @param ScrollTarget $to Destination for this movement. Select position, coordinates, or selector
     *     using its type discriminator.
     * @param int $durationMs Required movement duration in milliseconds. Must be at least 1. The recording
     *     deadline can cut it short.
     * @param ScrollEasing|null $easing Movement easing. Defaults to linear.
     */
    public function __construct(
        public ScrollTarget $to,
        public int $durationMs,
        public ?ScrollEasing $easing = null,
    ) {
    }
}
