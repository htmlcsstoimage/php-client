<?php

declare(strict_types=1);

namespace HtmlCssToImage\Render;

use HtmlCssToImage\ImageFormat;
use InvalidArgumentException;

/**
 * Options applied when an HCTI image URL is retrieved.
 *
 * Cropping happens before output resizing. These options also work with
 * signed template and create-and-render URLs.
 *
 * @api
 */
final readonly class RenderImageOptions
{
    /**
     * @param ImageFormat|null $format Output file format.
     * @param int|null $dpi Output DPI, greater than 30 and less than 600.
     * @param int|null $height Output height from 1 to 5000 pixels.
     * @param int|null $width Output width from 1 to 5000 pixels.
     * @param RenderImageCrop|null $crop Crop applied before resizing.
     */
    public function __construct(
        public ?ImageFormat $format = null,
        public ?int $dpi = null,
        public ?int $height = null,
        public ?int $width = null,
        public ?RenderImageCrop $crop = null,
    ) {
        if ($dpi !== null && ($dpi <= 30 || $dpi >= 600)) {
            throw new InvalidArgumentException(
                'DPI must be greater than 30 and less than 600',
            );
        }
        foreach (['height' => $height, 'width' => $width] as $name => $value) {
            if ($value !== null && ($value < 1 || $value > 5000)) {
                throw new InvalidArgumentException(
                    "Render {$name} must be from 1 to 5000 pixels",
                );
            }
        }
    }
}
