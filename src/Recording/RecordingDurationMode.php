<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Controls how the requested duration limits the recording timeline.
 * @api
 */
enum RecordingDurationMode: string
{
    /** Pad or cut off at the requested duration. */
    case Fixed = 'fixed';
    /** Adjust playback speed to fit the completed timeline. */
    case Target = 'target';
    /** Record for at least the requested duration, letting actions finish up to 90 seconds. */
    case AtLeast = 'at_least';
    /** Stop when actions finish or the requested duration is reached. This is the default. */
    case AtMost = 'at_most';
}
