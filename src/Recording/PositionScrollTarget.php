<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Scroll to an edge of the document or selected container.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class PositionScrollTarget extends ScrollTarget
{
    /**
     * @param ScrollPosition|null $position Edge to scroll to. Defaults to top.
     */
    public function __construct(
        public ?ScrollPosition $position = null,
    ) {
        parent::__construct('position');
    }
}
