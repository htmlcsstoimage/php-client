<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** A soundtrack from a public URL.
 * @see https://docs.htmlcsstoimage.com/parameters/audio/
 * @api
 */
final readonly class RecordingUrlAudio extends RecordingAudio
{
    /** @param string $url A public HTTP or HTTPS URL serving the audio file at render time.
     * @see RecordingAudio::__construct() for playback options.
     */
    public function __construct(
        public string $url,
        ?int $sourceStartMs = null,
        ?int $delayMs = null,
        ?RecordingAudioEndBehavior $whenAudioEnds = null,
        ?bool $errorOnSourceInvalid = null,
    ) {
        parent::__construct($sourceStartMs, $delayMs, $whenAudioEnds, $errorOnSourceInvalid);
    }
}
