<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Recording media format. MP4 and WebM support an optional soundtrack.
 * @api
 */
enum RecordingFormat: string
{
    case MP4 = 'mp4';
    case WebM = 'webm';
    case GIF = 'gif';
}
