<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Scroll through an ordered sequence of timed movements in the document or a selected container.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingScrollAction extends RecordingAction
{
    /**
     * @param list<ScrollStep> $steps Ordered movements. Supply at least one step.
     * @param string|null $containerSelector Scrollable container's CSS selector. Omit or use null to
     *     scroll the document.
     * @param list<RecordingAction>|null $ifNotFound Fallback when the container is missing. Omitted,
     *     null, or empty skips this action.
     */
    public function __construct(
        public array $steps,
        public ?string $containerSelector = null,
        public ?array $ifNotFound = null,
    ) {
        parent::__construct('scroll');
    }
}
