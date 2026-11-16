<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Edge of the document or selected scroll container.
 * @api
 */
enum ScrollPosition: string
{
    case Top = 'top';
    case Bottom = 'bottom';
    case Left = 'left';
    case Right = 'right';
}
