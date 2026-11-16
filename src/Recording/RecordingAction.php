<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Base type for ordered timeline interactions.
 * @api
 */
abstract readonly class RecordingAction
{
    public function __construct(public string $type)
    {
    }
}
