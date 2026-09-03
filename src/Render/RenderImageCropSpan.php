<?php

declare(strict_types=1);

namespace HtmlCssToImage\Render;

use InvalidArgumentException;

/**
 * Crop instructions for one horizontal or vertical axis.
 *
 * Construct a span with fromPosition(), between(), sizedFrom(), or sized().
 *
 * @api
 */
final readonly class RenderImageCropSpan
{
    /**
     * @param RenderImageCropPosition|null $start First crop boundary.
     * @param RenderImageCropPosition|null $end Second crop boundary.
     * @param RenderImageCropSize|null $size Crop width or height.
     * @param 'start'|'center'|'end' $origin Origin for a size without a start.
     */
    private function __construct(
        public ?RenderImageCropPosition $start = null,
        public ?RenderImageCropPosition $end = null,
        public ?RenderImageCropSize $size = null,
        public string $origin = 'start',
    ) {
    }

    /**
     * Crop from a position through the remaining image.
     *
     * @param RenderImageCropPosition $position First crop boundary.
     */
    public static function fromPosition(RenderImageCropPosition $position): self
    {
        return new self(start: $position);
    }

    /**
     * Crop between two positions, which may use different units.
     *
     * @param RenderImageCropPosition $start First crop boundary.
     * @param RenderImageCropPosition $end Second boundary; it must be greater
     *        than start when both positions use the same unit.
     */
    public static function between(
        RenderImageCropPosition $start,
        RenderImageCropPosition $end,
    ): self {
        if ($start->unit === $end->unit && $end->value <= $start->value) {
            throw new InvalidArgumentException(
                'Crop end must be greater than crop start',
            );
        }

        return new self(start: $start, end: $end);
    }

    /**
     * Crop a fixed size beginning at a position.
     *
     * @param RenderImageCropPosition $start First crop boundary.
     * @param RenderImageCropSize $size Width or height to retain.
     */
    public static function sizedFrom(
        RenderImageCropPosition $start,
        RenderImageCropSize $size,
    ): self {
        return new self(start: $start, size: $size);
    }

    /**
     * Crop a fixed size from the start, center, or end of an axis.
     *
     * @param RenderImageCropSize $size Width or height to retain.
     * @param 'start'|'center'|'end' $origin Crop origin.
     */
    public static function sized(
        RenderImageCropSize $size,
        string $origin = 'start',
    ): self {
        self::validateOrigin($origin);

        return new self(size: $size, origin: $origin);
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
