<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\Recording\RecordingAction;
use HtmlCssToImage\Recording\RecordingAudio;
use HtmlCssToImage\Recording\RecordingDurationMode;
use HtmlCssToImage\Recording\RecordingOutputOptions;
use HtmlCssToImage\Recording\RecordingTrigger;

/** Record a public HTTP or HTTPS webpage. @api */
final readonly class CreateUrlRecordingRequest extends BaseCreateRecordingRequest
{
    /**
     * @param string $url Public HTTP or HTTPS URL to record. See
     *     https://docs.htmlcsstoimage.com/parameters/url/
     * @param int $durationMs Requested duration in milliseconds: 500–30000, or up to 90000 in target
     *     mode. duration_mode controls how actions fit into this duration. See
     *     https://docs.htmlcsstoimage.com/parameters/duration_ms/
     * @param bool|null $blockConsentBanners Attempt to hide cookie and consent banners before recording.
     *     See https://docs.htmlcsstoimage.com/guides/advanced/blocking-cookie-banners/
     * @param bool|null $identifyAsHcti Add X-HCTI-SCREENSHOT: 1 to the top-level navigation. See
     *     https://docs.htmlcsstoimage.com/parameters/identify_as_hcti/
     * @param array<string, string>|null $headers Custom headers for top-level navigations to the URL's
     *     origin and additional_header_origins. See https://docs.htmlcsstoimage.com/parameters/headers/
     * @param list<string>|null $additionalHeaderOrigins Additional exact HTTP or HTTPS origins allowed
     *     to receive custom headers. See
     *     https://docs.htmlcsstoimage.com/parameters/headers/#additional-header-origins
     * @param bool|null $includeHeadersOnSubrequests Send custom headers with subrequests to allowed
     *     origins. Defaults to false. See
     *     https://docs.htmlcsstoimage.com/parameters/headers/#include-headers-on-subrequests
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
     *     at 30 fps. See https://docs.htmlcsstoimage.com/parameters/output/
     * @param RecordingAudio|null $audio Optional MP4 or WebM soundtrack from an asset ID or public URL.
     *     See https://docs.htmlcsstoimage.com/parameters/audio/
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
        public string $url,
        int $durationMs,
        public ?bool $blockConsentBanners = null,
        public ?bool $identifyAsHcti = null,
        public ?array $headers = null,
        public ?array $additionalHeaderOrigins = null,
        public ?bool $includeHeadersOnSubrequests = null,
        ?string $css = null,
        int|float|null $deviceScale = null,
        ?int $viewportHeight = null,
        ?int $viewportWidth = null,
        ?bool $disableTwemoji = null,
        ?int $dedupeDurationS = null,
        ?string $colorScheme = null,
        ?string $timezone = null,
        ?bool $viewportMobile = null,
        ?bool $viewportLandscape = null,
        ?bool $viewportTouch = null,
        ?string $mediaType = null,
        ?string $proxyId = null,
        ?string $storageDestinationId = null,
        ?array $requestOverrides = null,
        ?RecordingTrigger $trigger = null,
        ?array $actions = null,
        ?RecordingOutputOptions $output = null,
        ?RecordingDurationMode $durationMode = null,
        ?bool $deferRender = null,
        ?bool $skipScrollPreload = null,
        ?RecordingAudio $audio = null,
    ) {
        parent::__construct(
            durationMs: $durationMs,
            css: $css,
            deviceScale: $deviceScale,
            viewportHeight: $viewportHeight,
            viewportWidth: $viewportWidth,
            disableTwemoji: $disableTwemoji,
            dedupeDurationS: $dedupeDurationS,
            colorScheme: $colorScheme,
            timezone: $timezone,
            viewportMobile: $viewportMobile,
            viewportLandscape: $viewportLandscape,
            viewportTouch: $viewportTouch,
            mediaType: $mediaType,
            proxyId: $proxyId,
            storageDestinationId: $storageDestinationId,
            requestOverrides: $requestOverrides,
            trigger: $trigger,
            actions: $actions,
            output: $output,
            durationMode: $durationMode,
            deferRender: $deferRender,
            skipScrollPreload: $skipScrollPreload,
            audio: $audio,
        );
    }
}
