<?php

declare(strict_types=1);

namespace HtmlCssToImage\Render;

use InvalidArgumentException;

/**
 * Rectangle or aspect-ratio crop applied when an image URL is fetched.
 *
 * Construct a crop with rectangle(), aspectRatioFromWidth(), or
 * aspectRatioFromHeight().
 *
 * @api
 */
final readonly class RenderImageCrop
{
    /**
     * @param RenderImageCropSpan|null $horizontal Horizontal crop span.
     * @param RenderImageCropSpan|null $vertical Vertical crop span.
     * @param RenderImageAspectRatio|null $aspectRatio Output aspect ratio.
     * @param 'width'|'height'|null $aspectRatioAxis Axis that determines size.
     * @param 'start'|'center'|'end' $computedOrigin Origin on calculated axis.
     */
    private function __construct(
        public ?RenderImageCropSpan $horizontal = null,
        public ?RenderImageCropSpan $vertical = null,
        public ?RenderImageAspectRatio $aspectRatio = null,
        public ?string $aspectRatioAxis = null,
        public string $computedOrigin = 'start',
    ) {
    }

    /**
     * Create a rectangular crop from one or two axis spans.
     *
     * @param RenderImageCropSpan|null $horizontal Horizontal crop span.
     * @param RenderImageCropSpan|null $vertical Vertical crop span.
     */
    public static function rectangle(
        ?RenderImageCropSpan $horizontal = null,
        ?RenderImageCropSpan $vertical = null,
    ): self {
        if ($horizontal === null && $vertical === null) {
            throw new InvalidArgumentException(
                'A rectangle crop requires at least one axis',
            );
        }

        return new self(horizontal: $horizontal, vertical: $vertical);
    }

    /**
     * Calculate crop height from an aspect ratio and horizontal span.
     *
     * @param RenderImageAspectRatio $aspectRatio Desired aspect ratio.
     * @param RenderImageCropSpan $horizontal Span that determines crop width.
     * @param 'start'|'center'|'end' $heightOrigin Origin for calculated height.
     */
    public static function aspectRatioFromWidth(
        RenderImageAspectRatio $aspectRatio,
        RenderImageCropSpan $horizontal,
        string $heightOrigin = 'start',
    ): self {
        self::validateOrigin($heightOrigin);

        return new self(
            horizontal: $horizontal,
            aspectRatio: $aspectRatio,
            aspectRatioAxis: 'width',
            computedOrigin: $heightOrigin,
        );
    }

    /**
     * Calculate crop width from an aspect ratio and vertical span.
     *
     * @param RenderImageAspectRatio $aspectRatio Desired aspect ratio.
     * @param RenderImageCropSpan $vertical Span that determines crop height.
     * @param 'start'|'center'|'end' $widthOrigin Origin for calculated width.
     */
    public static function aspectRatioFromHeight(
        RenderImageAspectRatio $aspectRatio,
        RenderImageCropSpan $vertical,
        string $widthOrigin = 'start',
    ): self {
        self::validateOrigin($widthOrigin);

        return new self(
            vertical: $vertical,
            aspectRatio: $aspectRatio,
            aspectRatioAxis: 'height',
            computedOrigin: $widthOrigin,
        );
    }

    /** @param string $origin Crop origin to validate. */
    private static function validateOrigin(string $origin): void
    {
        if (!in_array($origin, ['start', 'center', 'end'], true)) {
            throw new InvalidArgumentException(
                'Crop origin must be start, center, or end',
            );
        }
    }
}
