<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use HtmlCssToImage\Request\BaseCreateImageRequest;
use HtmlCssToImage\Request\CreateHtmlCssImageRequest;
use HtmlCssToImage\Request\CreateImageRequest;
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\Request\PDFOptions;
use HtmlCssToImage\Request\PDFValueWithUnits;
use InvalidArgumentException;

/** @internal */
final class RequestMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(
        CreateImageRequest $request,
        bool $inBatch = false,
    ): array {
        return match (true) {
            $request instanceof CreateHtmlCssImageRequest
                => $this->mapHtmlRequest($request, $inBatch),
            $request instanceof CreateUrlImageRequest
                => $this->mapUrlRequest($request, $inBatch),
            $request instanceof CreateTemplatedImageRequest && !$inBatch
                => self::withoutNulls([
                    'template_id' => $request->templateId,
                    'template_version' => $request->templateVersion,
                    'template_values' => $request->templateValues,
                    'format' => $request->format?->value,
                ]),
            default => throw new InvalidArgumentException(
                sprintf('Unsupported request type: %s', $request::class),
            ),
        };
    }

    /**
     * Map options shared by HTML/CSS and URL requests.
     *
     * This method is public only for the internal signed URL builder.
     *
     * @return array<string, mixed>
     */
    public function commonPayload(
        BaseCreateImageRequest $request,
        bool $includeDedupe = true,
    ): array {
        return self::withoutNulls([
            'format' => $request->format?->value,
            'selector' => $request->selector,
            'device_scale' => $request->deviceScale,
            'viewport_height' => $request->viewportHeight,
            'viewport_width' => $request->viewportWidth,
            'max_wait_ms' => $request->maxWaitMs,
            'ms_delay' => $request->msDelay,
            'render_when_ready' => $request->renderWhenReady,
            'max_render_once' => $request->maxRenderOnce,
            'disable_twemoji' => $request->disableTwemoji,
            'color_scheme' => $request->colorScheme,
            'timezone' => $request->timezone,
            'viewport_mobile' => $request->viewportMobile,
            'viewport_touch' => $request->viewportTouch,
            'viewport_landscape' => $request->viewportLandscape,
            'media_type' => $request->mediaType,
            'proxy_id' => $request->proxyId,
            'jumbo_max_width' => $request->jumboMaxWidth,
            'jumbo_max_height' => $request->jumboMaxHeight,
            'dedupe_duration_s' => $includeDedupe
                ? $request->dedupeDurationS
                : null,
            'storage_destination_id' => $request->storageDestinationId,
            'transparent_background' => $request->transparentBackground,
            'pdf_options' => $this->mapPdfOptions($request->pdfOptions),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapHtmlRequest(
        CreateHtmlCssImageRequest $request,
        bool $inBatch,
    ): array {
        $payload = $this->commonPayload($request, !$inBatch);
        $payload['html'] = $inBatch && $request->html === ''
            ? null
            : $request->html;
        $payload['css'] = $request->css;

        if ($request->googleFonts !== null && $request->googleFonts !== []) {
            $fonts = [];
            foreach ($request->googleFonts as $font) {
                $processed = str_replace(' ', '+', trim($font));
                if ($processed !== '' && !in_array($processed, $fonts, true)) {
                    $fonts[] = $processed;
                }
            }
            if ($fonts !== []) {
                $payload['google_fonts'] = implode('|', $fonts);
            }
        }

        return self::withoutNulls($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUrlRequest(
        CreateUrlImageRequest $request,
        bool $inBatch,
    ): array {
        $payload = $this->commonPayload($request, !$inBatch);
        $payload['url'] = $inBatch && $request->url === ''
            ? null
            : $request->url;
        $payload['css'] = $request->css;
        $payload['headers'] = $request->headers;
        $payload['additional_header_origins'] = $request->additionalHeaderOrigins;
        $payload['include_headers_on_subrequests'] = $request->includeHeadersOnSubrequests;
        $payload['identify_as_hcti'] = $request->identifyAsHcti;
        $payload['full_screen'] = $request->fullScreen;
        $payload['block_consent_banners'] = $request->blockConsentBanners;

        return self::withoutNulls($payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapPdfOptions(?PDFOptions $options): ?array
    {
        if ($options === null) {
            return null;
        }

        $payload = [
            'print_background' => $options->printBackground,
            'scale' => $options->scale,
            'page_height' => $options->pageHeight === null
                ? null
                : self::pdfValueToString($options->pageHeight),
            'page_width' => $options->pageWidth === null
                ? null
                : self::pdfValueToString($options->pageWidth),
        ];

        if ($options->margins !== null) {
            $payload['margins'] = [
                self::pdfValueToString($options->margins->top),
                self::pdfValueToString($options->margins->right),
                self::pdfValueToString($options->margins->bottom),
                self::pdfValueToString($options->margins->left),
            ];
        }

        return self::withoutNulls($payload);
    }

    private static function pdfValueToString(
        int|float|PDFValueWithUnits $value,
    ): string {
        if ($value instanceof PDFValueWithUnits) {
            return self::numberToString($value->value) . $value->unit;
        }

        return self::numberToString($value) . 'px';
    }

    private static function numberToString(int|float $value): string
    {
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return (string) $value;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private static function withoutNulls(array $values): array
    {
        return array_filter(
            $values,
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
