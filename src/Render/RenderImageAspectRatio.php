<?php

declare(strict_types=1);

namespace HtmlCssToImage\Render;

use InvalidArgumentException;

/**
 * Positive width and height components of a crop aspect ratio.
 *
 * @api
 */
final readonly class RenderImageAspectRatio
{
    /**
     * @param int $width Positive width component, such as 16 in 16:9.
     * @param int $height Positive height component, such as 9 in 16:9.
     */
    public function __construct(
        public int $width,
        public int $height,
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException(
                'Aspect ratio width and height must be positive',
            );
        }
    }
}
