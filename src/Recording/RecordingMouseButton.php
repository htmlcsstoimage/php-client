<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Mouse button used by a click action.
 * @api
 */
enum RecordingMouseButton: string
{
    case Left = 'left';
    case Right = 'right';
    case Middle = 'middle';
    case Back = 'back';
    case Forward = 'forward';
}
