<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/**
 * A numeric PDF dimension paired with an explicit unit.
 *
 * @api
 */
final readonly class PDFValueWithUnits
{
    /**
     * @param int|float $value Numeric magnitude of the dimension.
     * @param 'px'|'in'|'cm'|'mm' $unit Unit used by the dimension.
     */
    public function __construct(
        public int|float $value,
        public string $unit,
    ) {
    }
}
