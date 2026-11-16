<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Start recording immediately after page preparation. This is the default trigger.
 * @see https://docs.htmlcsstoimage.com/parameters/trigger/
 * @api
 */
final readonly class RecordingImmediateTrigger extends RecordingTrigger
{
    /**
     */
    public function __construct(
    ) {
        parent::__construct('immediate');
    }
}
