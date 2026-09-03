<?php

declare(strict_types=1);

namespace HtmlCssToImage\Render;

use InvalidArgumentException;

/**
 * A crop boundary expressed in pixels or as a percentage.
 *
 * Use pixels() or percent() to construct a validated value.
 *
 * @api
 */
final readonly class RenderImageCropPosition
{
    /**
     * @param int $value Non-negative pixels or a percentage from 1 to 100.
     * @param 'px'|'%' $unit Unit used by the position.
     */
    private function __construct(
        public int $value,
        public string $unit,
    ) {
        if ($unit === 'px' && $value < 0) {
            throw new InvalidArgumentException(
                'A pixel crop position cannot be negative',
            );
        }
        if ($unit === '%' && ($value < 1 || $value > 100)) {
            throw new InvalidArgumentException(
                'A percentage crop position must be from 1 to 100',
            );
        }
    }

    /** Create a crop position measured in non-negative pixels. */
    public static function pixels(int $value): self
    {
        return new self($value, 'px');
    }

    /** Create a crop position measured as a percentage from 1 to 100. */
    public static function percent(int $value): self
    {
        return new self($value, '%');
    }
}
