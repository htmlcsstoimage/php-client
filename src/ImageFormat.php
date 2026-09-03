<?php

declare(strict_types=1);

namespace HtmlCssToImage;

/**
 * File format used in image URLs returned or built by the client.
 *
 * @api
 */
enum ImageFormat: string
{
    /** Portable Network Graphics. */
    case PNG = 'png';

    /** Joint Photographic Experts Group. */
    case JPG = 'jpg';

    /** WebP image format. */
    case WEBP = 'webp';

    /** Portable Document Format. */
    case PDF = 'pdf';
}
