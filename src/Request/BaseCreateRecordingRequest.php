<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\Recording\RecordingAction;
use HtmlCssToImage\Recording\RecordingDurationMode;
use HtmlCssToImage\Recording\RecordingOutputOptions;
use HtmlCssToImage\Recording\RecordingTrigger;

/** Shared browser settings, timeline, and output options. Null uses API defaults.
 * @see https://docs.htmlcsstoimage.com/getting-started/recordings/
 * @api
 */
abstract readonly class BaseCreateRecordingRequest implements CreateRecordingRequest
{
    /**
     * @param int $durationMs Requested duration in milliseconds: 500–30000, or up to 90000 in target
     *     mode. duration_mode controls how actions fit into this duration. See
     *     https://docs.htmlcsstoimage.com/parameters/duration_ms/
     * @param string|null $css CSS to style HTML content or inject into a URL page after it loads. See
     *     https://docs.htmlcsstoimage.com/parameters/css/
     * @param int|float|null $deviceScale Capture pixel density. Defaults to 2 for HTML/CSS and 1 for URL
     *     recordings. See https://docs.htmlcsstoimage.com/parameters/device_scale/
     * @param int|null $viewportHeight Viewport height in CSS pixels. Supply both dimensions to set the
     *     recording size. See https://docs.htmlcsstoimage.com/parameters/viewport/
     * @param int|null $viewportWidth Viewport width in CSS pixels. Supply both dimensions to set the
     *     recording size. See https://docs.htmlcsstoimage.com/parameters/viewport/
     * @param bool|null $disableTwemoji Disable Twemoji for HTML/CSS when true. URL recordings inject it
     *     only when explicitly false. See https://docs.htmlcsstoimage.com/parameters/disable_twemoji/
     * @param int|null $dedupeDurationS Reuse an identical recording created within this many seconds.
     *     Set to 0 to disable deduplication. See
     *     https://docs.htmlcsstoimage.com/parameters/dedupe_duration_s/
     * @param 'light'|'dark'|null $colorScheme Chrome's preferred light or dark color scheme while
     *     recording. See https://docs.htmlcsstoimage.com/parameters/color_scheme/
     * @param string|null $timezone Browser timezone as an IANA identifier, such as 'America/New_York'.
     *     See https://docs.htmlcsstoimage.com/parameters/timezone/
     * @param bool|null $viewportMobile Enable mobile viewport behavior, including the page's viewport
     *     meta tag. See https://docs.htmlcsstoimage.com/parameters/viewport/
     * @param bool|null $viewportLandscape Emulate landscape orientation. See
     *     https://docs.htmlcsstoimage.com/parameters/viewport/
     * @param bool|null $viewportTouch Emulate a viewport that supports touch events. See
     *     https://docs.htmlcsstoimage.com/parameters/viewport/
     * @param 'print'|'screen'|null $mediaType CSS media type used while recording. See
     *     https://docs.htmlcsstoimage.com/parameters/media_type/
     * @param string|null $proxyId Organization proxy to use while recording. See
     *     https://docs.htmlcsstoimage.com/guides/advanced/proxies/
     * @param string|null $storageDestinationId Storage destination to receive the recording. See
     *     https://docs.htmlcsstoimage.com/guides/advanced/storage-destinations/
     * @param list<RequestOverride>|null $requestOverrides Block matching browser requests while
     *     recording. Available on paid plans. See
     *     https://docs.htmlcsstoimage.com/parameters/request_overrides/
     * @param RecordingTrigger|null $trigger When recording and actions start after page preparation.
     *     Defaults to immediate. See https://docs.htmlcsstoimage.com/parameters/trigger/
     * @param list<RecordingAction>|null $actions Ordered interactions. Leave null or empty to record the
     *     page without interactions. See https://docs.htmlcsstoimage.com/parameters/actions/
     * @param RecordingOutputOptions|null $output Output format, frame rate, and scale. Defaults to MP4
     *     at 30 fps without audio. See https://docs.htmlcsstoimage.com/parameters/output/
     * @param RecordingDurationMode|null $durationMode How duration_ms limits the timeline. Defaults to
     *     at_most. See https://docs.htmlcsstoimage.com/parameters/duration_mode/
     * @param bool|null $deferRender Wait to render until media or storage is requested. Defaults to
     *     false; does not reset deduplicated recordings. See
     *     https://docs.htmlcsstoimage.com/parameters/defer_render/
     * @param bool|null $skipScrollPreload Skip the preparation scroll that loads lazy content. Scrolling
     *     actions still run. Defaults to false. See
     *     https://docs.htmlcsstoimage.com/parameters/skip_scroll_preload/
     */
    public function __construct(
        public int $durationMs,
        public ?string $css = null,
        public int|float|null $deviceScale = null,
        public ?int $viewportHeight = null,
        public ?int $viewportWidth = null,
        public ?bool $disableTwemoji = null,
        public ?int $dedupeDurationS = null,
        public ?string $colorScheme = null,
        public ?string $timezone = null,
        public ?bool $viewportMobile = null,
        public ?bool $viewportLandscape = null,
        public ?bool $viewportTouch = null,
        public ?string $mediaType = null,
        public ?string $proxyId = null,
        public ?string $storageDestinationId = null,
        public ?array $requestOverrides = null,
        public ?RecordingTrigger $trigger = null,
        public ?array $actions = null,
        public ?RecordingOutputOptions $output = null,
        public ?RecordingDurationMode $durationMode = null,
        public ?bool $deferRender = null,
        public ?bool $skipScrollPreload = null,
    ) {
    }
}
