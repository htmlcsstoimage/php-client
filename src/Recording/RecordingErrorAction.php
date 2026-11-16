<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Fail the recording and stop executing actions. Must be the last action in its array.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingErrorAction extends RecordingAction
{
    /**
     */
    public function __construct(
    ) {
        parent::__construct('error');
    }
}
