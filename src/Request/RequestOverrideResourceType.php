<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/** Browser network resource types supported by request overrides. */
enum RequestOverrideResourceType: string
{
    case Beacon = 'beacon';
    case Document = 'document';
    case Stylesheet = 'stylesheet';
    case Image = 'image';
    case ImageSet = 'image_set';
    case Media = 'media';
    case Font = 'font';
    case Script = 'script';
    case TextTrack = 'text_track';
    case Xhr = 'xhr';
    case Fetch = 'fetch';
    case EventSource = 'event_source';
    case Manifest = 'manifest';
    case Ping = 'ping';
    case Img = 'img';
    case Other = 'other';
}
