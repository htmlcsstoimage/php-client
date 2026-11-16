<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Base type for a scroll step destination.
 * @api
 */
abstract readonly class ScrollTarget
{
    public function __construct(public string $type)
    {
    }
}
