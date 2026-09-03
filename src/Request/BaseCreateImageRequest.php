<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\ImageFormat;

/**
 * Options shared by HTML/CSS and URL screenshot requests.
 *
 * All options are nullable. Null values are omitted from API payloads.
 *
 * @api
 */
abstract readonly class BaseCreateImageRequest implements BatchCreateImageRequest
{
    /**
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
     *        many seconds. Supported only by single POST requests.
     * @param string|null $storageDestinationId Configured HCTI storage
     *        destination identifier.
     * @param bool|null $transparentBackground Render a transparent background.
     * @param ImageFormat|null $format File format used in the returned URL.
     */
    public function __construct(
        public ?string $selector = null,
        public int|float|null $deviceScale = null,
        public ?int $viewportHeight = null,
        public ?int $viewportWidth = null,
        public ?int $maxWaitMs = null,
        public ?int $msDelay = null,
        public ?bool $renderWhenReady = null,
        public ?bool $maxRenderOnce = null,
        public ?bool $disableTwemoji = null,
        public ?string $colorScheme = null,
        public ?string $timezone = null,
        public ?bool $viewportMobile = null,
        public ?bool $viewportTouch = null,
        public ?bool $viewportLandscape = null,
        public ?string $mediaType = null,
        public ?string $proxyId = null,
        public ?int $jumboMaxWidth = null,
        public ?int $jumboMaxHeight = null,
        public ?PDFOptions $pdfOptions = null,
        public ?int $dedupeDurationS = null,
        public ?string $storageDestinationId = null,
        public ?bool $transparentBackground = null,
        public ?ImageFormat $format = null,
    ) {
    }
}
