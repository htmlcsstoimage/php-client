<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/**
 * Marker interface for HTML/CSS and URL requests accepted in image batches.
 *
 * Template requests do not implement this interface because the HCTI batch
 * endpoint does not support them.
 *
 * @api
 */
interface BatchCreateImageRequest extends CreateImageRequest
{
}
