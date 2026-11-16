<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Current lifecycle state. Ready, Failed, and Cancelled are terminal.
 * @api
 */
enum RecordingStatus: string
{
    case Processing = 'processing';
    case PendingRetry = 'pending_retry';
    case Ready = 'ready';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case AwaitingRender = 'awaiting_render';
    /** An unrecognized status returned by a newer API version. */
    case Unknown = 'unknown';
}
