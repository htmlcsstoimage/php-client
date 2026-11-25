<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** A single MP4 or WebM soundtrack. Choose RecordingAssetAudio or RecordingUrlAudio.
 * @see https://docs.htmlcsstoimage.com/parameters/audio/
 * @api
 */
abstract readonly class RecordingAudio
{
    /**
     * @param int|null $sourceStartMs Offset into the source file in milliseconds. Defaults to 0.
     * @param int|null $delayMs Delay from the start of the finished video in milliseconds. Defaults to 0.
     * @param RecordingAudioEndBehavior|null $whenAudioEnds Playback when the track ends. Defaults to Silence.
     * @param bool|null $errorOnSourceInvalid Defaults to true. Set false to return the full silent video
     *     if the track cannot play at render time.
     */
    protected function __construct(
        public ?int $sourceStartMs,
        public ?int $delayMs,
        public ?RecordingAudioEndBehavior $whenAudioEnds,
        public ?bool $errorOnSourceInvalid,
    ) {
    }
}
