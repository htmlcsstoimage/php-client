<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/**
 * Top, right, bottom, and left margins for a generated PDF.
 *
 * Plain numbers are interpreted as pixels. Use PDFValueWithUnits for inches,
 * centimeters, or millimeters.
 *
 * @api
 */
final readonly class PDFMargins
{
    /**
     * @param int|float|PDFValueWithUnits $top Top page margin.
     * @param int|float|PDFValueWithUnits $right Right page margin.
     * @param int|float|PDFValueWithUnits $bottom Bottom page margin.
     * @param int|float|PDFValueWithUnits $left Left page margin.
     */
    public function __construct(
        public int|float|PDFValueWithUnits $top,
        public int|float|PDFValueWithUnits $right,
        public int|float|PDFValueWithUnits $bottom,
        public int|float|PDFValueWithUnits $left,
    ) {
    }
}
