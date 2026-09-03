<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\ImageFormat;

/**
 * Request to capture a screenshot of a URL.
 *
 * URL defaults to an empty string so a batch variation can inherit it from
 * its batch defaults.
 *
 * @api
 */
final readonly class CreateUrlImageRequest extends BaseCreateImageRequest
{
    /**
     * @param string $url Public URL for the renderer to capture.
     * @param string|null $css Optional CSS injected into the target page.
     * @param bool|null $fullScreen Capture the entire scrollable page.
     * @param bool|null $blockConsentBanners Attempt to hide consent banners.
     * @param string|null $selector CSS selector to crop the image to.
     * @param int|float|null $deviceScale Browser device scale factor.
     * @param int|null $viewportHeight Browser viewport height in pixels.
     * @param int|null $viewportWidth Browser viewport width in pixels.
     * @param int|null $maxWaitMs Maximum renderer wait in milliseconds.
     * @param int|null $msDelay Additional pre-capture delay in milliseconds.
     * @param bool|null $renderWhenReady Wait for ScreenshotReady() in page JS.
     * @param bool|null $maxRenderOnce Render and save the image only once.
     * @param bool|null $disableTwemoji Disable Twemoji fallback rendering.
     * @param 'light'|'dark'|null $colorScheme Emulated color scheme.
     * @param string|null $timezone Browser IANA timezone.
     * @param bool|null $viewportMobile Emulate a mobile viewport.
     * @param bool|null $viewportTouch Enable viewport touch interactions.
     * @param bool|null $viewportLandscape Use landscape orientation.
     * @param 'print'|'screen'|null $mediaType Emulated CSS media type.
     * @param string|null $proxyId Organization proxy identifier.
     * @param int|null $jumboMaxWidth Jumbo-mode maximum width.
     * @param int|null $jumboMaxHeight Jumbo-mode maximum height.
     * @param PDFOptions|null $pdfOptions PDF output settings.
     * @param int|null $dedupeDurationS Reuse a matching recent image for this
     *        many seconds. Single POST requests only.
     * @param string|null $storageDestinationId Configured HCTI storage
     *        destination identifier.
     * @param bool|null $transparentBackground Render a transparent background.
     * @param array<string, string>|null $headers Headers sent to the target URL.
     *        Do not put secrets here when generating a signed URL.
     * @param list<string>|null $additionalHeaderOrigins Exact additional origins
     *        allowed to receive headers on subrequests.
     * @param bool|null $includeHeadersOnSubrequests Send headers to same-origin
     *        subrequests.
     * @param bool|null $identifyAsHcti Add HCTI's identifying request header.
     * @param ImageFormat|null $format File format used in the returned URL.
     */
    public function __construct(
        public string $url = '',
        public ?string $css = null,
        public ?bool $fullScreen = null,
        public ?bool $blockConsentBanners = null,
        ?string $selector = null,
        int|float|null $deviceScale = null,
        ?int $viewportHeight = null,
        ?int $viewportWidth = null,
        ?int $maxWaitMs = null,
        ?int $msDelay = null,
        ?bool $renderWhenReady = null,
        ?bool $maxRenderOnce = null,
        ?bool $disableTwemoji = null,
        ?string $colorScheme = null,
        ?string $timezone = null,
        ?bool $viewportMobile = null,
        ?bool $viewportTouch = null,
        ?bool $viewportLandscape = null,
        ?string $mediaType = null,
        ?string $proxyId = null,
        ?int $jumboMaxWidth = null,
        ?int $jumboMaxHeight = null,
        ?PDFOptions $pdfOptions = null,
        ?int $dedupeDurationS = null,
        ?string $storageDestinationId = null,
        ?bool $transparentBackground = null,
        public ?array $headers = null,
        public ?array $additionalHeaderOrigins = null,
        public ?bool $includeHeadersOnSubrequests = null,
        public ?bool $identifyAsHcti = null,
        ?ImageFormat $format = null,
    ) {
        parent::__construct(
            selector: $selector,
            deviceScale: $deviceScale,
            viewportHeight: $viewportHeight,
            viewportWidth: $viewportWidth,
            maxWaitMs: $maxWaitMs,
            msDelay: $msDelay,
            renderWhenReady: $renderWhenReady,
            maxRenderOnce: $maxRenderOnce,
            disableTwemoji: $disableTwemoji,
            colorScheme: $colorScheme,
            timezone: $timezone,
            viewportMobile: $viewportMobile,
            viewportTouch: $viewportTouch,
            viewportLandscape: $viewportLandscape,
            mediaType: $mediaType,
            proxyId: $proxyId,
            jumboMaxWidth: $jumboMaxWidth,
            jumboMaxHeight: $jumboMaxHeight,
            pdfOptions: $pdfOptions,
            dedupeDurationS: $dedupeDurationS,
            storageDestinationId: $storageDestinationId,
            transparentBackground: $transparentBackground,
            format: $format,
        );
    }
}
