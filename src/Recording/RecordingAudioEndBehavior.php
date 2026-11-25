<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Playback when the track ends before the video.
 * @api
 */
enum RecordingAudioEndBehavior: string
{
    /** Play the rest of the video silently (default). */
    case Silence = 'silence';
    /** Repeat the track from the selected source offset. */
    case Loop = 'loop';
    /** End the video with the track. */
    case TrimVideo = 'trim_video';
}
