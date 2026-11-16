<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Scroll to absolute coordinates within the document or selected container.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class CoordinatesScrollTarget extends ScrollTarget
{
    /**
     * @param int|float|null $x Horizontal position. Defaults to 0.
     * @param int|float|null $y Vertical position. Defaults to 0.
     */
    public function __construct(
        public int|float|null $x = null,
        public int|float|null $y = null,
    ) {
        parent::__construct('coordinates');
    }
}
