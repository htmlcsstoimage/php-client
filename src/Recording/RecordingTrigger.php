<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Base type for the condition that starts capture and actions.
 * @api
 */
abstract readonly class RecordingTrigger
{
    public function __construct(public string $type)
    {
    }
}
