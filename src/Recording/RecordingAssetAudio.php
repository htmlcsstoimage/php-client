<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** A soundtrack from the Asset Library.
 * @see https://docs.htmlcsstoimage.com/parameters/audio/
 * @api
 */
final readonly class RecordingAssetAudio extends RecordingAudio
{
    /** @param string $assetId The ID of a completed audio upload in the recording organization's Asset Library. Private files are supported.
     * @see RecordingAudio::__construct() for playback options.
     */
    public function __construct(
        public string $assetId,
        ?int $sourceStartMs = null,
        ?int $delayMs = null,
        ?RecordingAudioEndBehavior $whenAudioEnds = null,
        ?bool $errorOnSourceInvalid = null,
    ) {
        parent::__construct($sourceStartMs, $delayMs, $whenAudioEnds, $errorOnSourceInvalid);
    }
}
