<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/**
 * Options that cause the API to render a PDF instead of an image.
 *
 * @api
 */
final readonly class PDFOptions
{
    /**
     * @param bool|null $printBackground Include CSS background graphics.
     * @param int|float|null $scale Scale applied to the rendered page.
     * @param PDFMargins|null $margins Page margins.
     * @param int|float|PDFValueWithUnits|null $pageHeight Page height. Plain
     *        numbers are interpreted as pixels.
     * @param int|float|PDFValueWithUnits|null $pageWidth Page width. Plain
     *        numbers are interpreted as pixels.
     */
    public function __construct(
        public ?bool $printBackground = null,
        public int|float|null $scale = null,
        public ?PDFMargins $margins = null,
        public int|float|PDFValueWithUnits|null $pageHeight = null,
        public int|float|PDFValueWithUnits|null $pageWidth = null,
    ) {
    }
}
