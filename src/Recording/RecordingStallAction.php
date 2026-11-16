<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Wait while recording and page activity continue.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingStallAction extends RecordingAction
{
    /**
     * @param int|null $durationMs Wait in milliseconds. The recording deadline can end it early.
     */
    public function __construct(
        public ?int $durationMs = null,
    ) {
        parent::__construct('stall');
    }
}
