<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/** Browser request override action. */
enum RequestOverrideAction: string
{
    case Block = 'block';
}
