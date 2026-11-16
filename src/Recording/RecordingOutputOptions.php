<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Output format, frame rate, and scaling for a recording.
 * @see https://docs.htmlcsstoimage.com/parameters/output/
 * @api
 */
final readonly class RecordingOutputOptions
{
    /**
     * @param RecordingFormat|null $format Media format. Defaults to mp4.
     * @param int|null $fps Frames per second. Defaults to 15 for GIF and 30 for video. GIF supports
     *     10–50; video supports 10–120.
     * @param int|float|null $scale Scales captured pixels without changing page layout. Defaults to 1;
     *     0.5 halves both dimensions.
     */
    public function __construct(
        public ?RecordingFormat $format = null,
        public ?int $fps = null,
        public int|float|null $scale = null,
    ) {
    }
}
