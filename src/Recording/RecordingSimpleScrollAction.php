<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Scroll down repeatedly until reaching the bottom or the recording deadline. Each movement has its own duration and easing, followed by a pause.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingSimpleScrollAction extends RecordingAction
{
    /**
     * @param int|null $scrollDurationMs Duration of each movement in milliseconds. Defaults to 800.
     * @param int|null $msBetweenScrolls Pause between movements in milliseconds. Defaults to 400; 0
     *     scrolls continuously.
     * @param int|float|string|null $scrollDistance CSS pixels as a number, or a string such as '900px'
     *     or '50vh'. Omit or use null to use API defaults.
     * @param ScrollEasing|null $easing Movement easing. Omit or use null to use API defaults.
     * @param int|null $stallAtEndMs Time to hold at the bottom in milliseconds, limited by remaining
     *     recording time. Defaults to 0.
     */
    public function __construct(
        public ?int $scrollDurationMs = null,
        public ?int $msBetweenScrolls = null,
        public int|float|string|null $scrollDistance = null,
        public ?ScrollEasing $easing = null,
        public ?int $stallAtEndMs = null,
    ) {
        parent::__construct('simple_scroll');
    }
}
