<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\Render\RenderImageCrop;
use HtmlCssToImage\Render\RenderImageCropSpan;
use HtmlCssToImage\Render\RenderImageOptions;
use InvalidArgumentException;
use JsonException;

/** @internal */
final readonly class UrlBuilder
{
    public function __construct(
        private string $baseUrl,
        private string $apiId,
        private string $apiKey,
        private RequestMapper $requestMapper,
    ) {
    }

    public function image(
        string $imageId,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        $options = $renderOptions ?? new RenderImageOptions();
        $path = "{$this->baseUrl}/v1/image/" . self::pathEncodeComponent($imageId);
        if ($options->format !== null) {
            $path .= ".{$options->format->value}";
        }

        $query = self::formEncode(self::renderOptionPairs($options));

        return $query === '' ? $path : "{$path}?{$query}";
    }

    /**
     * @param array<string, mixed> $templateValues
     *
     * @throws JsonException When a template value cannot be JSON encoded.
     */
    public function templated(
        string|CreateTemplatedImageRequest $templateIdOrRequest,
        array $templateValues = [],
        ?int $templateVersion = null,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        $request = is_string($templateIdOrRequest)
            ? new CreateTemplatedImageRequest(
                templateId: $templateIdOrRequest,
                templateValues: $templateValues,
                templateVersion: $templateVersion,
            )
            : $templateIdOrRequest;

        /** @var list<array{0: string, 1: string}> $pairs */
        $pairs = [];
        if ($request->templateVersion !== null && $request->templateVersion !== 0) {
            $pairs[] = ['template_version', (string) $request->templateVersion];
        }

        $values = $request->templateValues;
        ksort($values, SORT_STRING);
        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            $encodedValue = is_array($value) || is_object($value)
                ? self::encodeJson($value)
                : self::javascriptString($value);
            $pairs[] = [(string) $key, $encodedValue];
        }

        $options = $renderOptions ?? new RenderImageOptions();
        /** @var array<string, true> $reservedKeys */
        $reservedKeys = [];
        foreach (array_keys($request->templateValues) as $key) {
            $reservedKeys[(string) $key] = true;
        }
        array_push(
            $pairs,
            ...self::renderOptionPairs($options, $reservedKeys),
        );

        $query = self::formEncode($pairs);
        $token = $this->generateHmacToken($query);
        $separator = $query === '' ? '' : '?';
        $format = $options->format ?? $request->format;
        $formatPath = $format === null ? '' : "/{$format->value}";

        return "{$this->baseUrl}/v1/image/{$request->templateId}/{$token}"
            . "{$formatPath}{$separator}{$query}";
    }

    public function createAndRender(
        CreateUrlImageRequest $request,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        /** @var list<array{0: string, 1: string}> $pairs */
        $pairs = [['url', $request->url]];
        $payload = $this->requestMapper->commonPayload($request, false);
        unset($payload['format'], $payload['pdf_options']);
        $payload['css'] = $request->css;
        $payload['headers'] = $request->headers;
        $payload['additional_header_origins'] = $request->additionalHeaderOrigins;
        $payload['include_headers_on_subrequests'] = $request->includeHeadersOnSubrequests;
        $payload['identify_as_hcti'] = $request->identifyAsHcti;
        $payload['full_screen'] = $request->fullScreen;
        $payload['block_consent_banners'] = $request->blockConsentBanners;
        ksort($payload, SORT_STRING);

        foreach ($payload as $key => $value) {
            if (
                $value === null
                || ($value === false && $key !== 'transparent_background')
            ) {
                continue;
            }
            if ($key === 'headers' && is_array($value)) {
                foreach ($value as $name => $headerValue) {
                    if (!is_string($name) || !is_string($headerValue)) {
                        continue;
                    }
                    $pairs[] = ['headers', "{$name}:{$headerValue}"];
                }
            } elseif ($key === 'additional_header_origins' && is_array($value)) {
                foreach ($value as $origin) {
                    if (is_string($origin)) {
                        $pairs[] = [$key, $origin];
                    }
                }
            } else {
                $pairs[] = [$key, self::javascriptString($value)];
            }
        }

        $options = $renderOptions ?? new RenderImageOptions();
        array_push($pairs, ...self::renderOptionPairs($options));
        $query = self::formEncode($pairs);
        $token = $this->generateHmacToken($query);
        $format = $options->format ?? $request->format;
        $formatPath = $format === null ? '' : "/{$format->value}";

        return "{$this->baseUrl}/v1/image/create-and-render/"
            . "{$this->apiId}/{$token}{$formatPath}?{$query}";
    }

    /**
     * @param array<string, true> $reservedKeys Template variable names.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function renderOptionPairs(
        RenderImageOptions $options,
        array $reservedKeys = [],
    ): array {
        $pairs = [];
        if ($options->dpi !== null) {
            $pairs[] = [self::renderKey('dpi', $reservedKeys), (string) $options->dpi];
        }
        if ($options->height !== null) {
            $pairs[] = [
                self::renderKey('height', $reservedKeys),
                (string) $options->height,
            ];
        }
        if ($options->width !== null) {
            $pairs[] = [
                self::renderKey('width', $reservedKeys),
                (string) $options->width,
            ];
        }
        if ($options->crop !== null) {
            array_push(
                $pairs,
                ...self::cropOptionPairs($options->crop, $reservedKeys),
            );
        }

        return $pairs;
    }

    /**
     * @param array<string, true> $reservedKeys Template variable names.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function cropOptionPairs(
        RenderImageCrop $crop,
        array $reservedKeys,
    ): array {
        $pairs = [];
        if ($crop->aspectRatio !== null) {
            $pairs[] = [
                self::renderKey('aspect_ratio', $reservedKeys),
                "{$crop->aspectRatio->width}_{$crop->aspectRatio->height}",
            ];
        }

        $xOrigin = self::cropOrigin($crop->horizontal);
        $yOrigin = self::cropOrigin($crop->vertical);
        if ($crop->aspectRatioAxis === 'height') {
            $xOrigin = $crop->computedOrigin;
        } elseif ($crop->aspectRatioAxis === 'width') {
            $yOrigin = $crop->computedOrigin;
        }
        if ($xOrigin !== 'start') {
            $pairs[] = [self::renderKey('x_origin', $reservedKeys), $xOrigin];
        }
        if ($yOrigin !== 'start') {
            $pairs[] = [self::renderKey('y_origin', $reservedKeys), $yOrigin];
        }

        self::appendCropSpan($pairs, $crop->horizontal, 'x', $reservedKeys);
        self::appendCropSpan($pairs, $crop->vertical, 'y', $reservedKeys);

        return $pairs;
    }

    /** @return 'start'|'center'|'end' */
    private static function cropOrigin(?RenderImageCropSpan $span): string
    {
        if ($span !== null && $span->start === null && $span->size !== null) {
            return $span->origin;
        }

        return 'start';
    }

    /**
     * @param list<array{0: string, 1: string}> $pairs
     * @param 'x'|'y' $axis Crop axis.
     * @param array<string, true> $reservedKeys Template variable names.
     */
    private static function appendCropSpan(
        array &$pairs,
        ?RenderImageCropSpan $span,
        string $axis,
        array $reservedKeys,
    ): void {
        if ($span === null) {
            return;
        }
        if ($span->start !== null) {
            $pairs[] = [
                self::renderKey("{$axis}_1", $reservedKeys),
                "{$span->start->value}{$span->start->unit}",
            ];
        }
        if ($span->end !== null) {
            $pairs[] = [
                self::renderKey("{$axis}_2", $reservedKeys),
                "{$span->end->value}{$span->end->unit}",
            ];
        }
        if ($span->size !== null) {
            $longName = $axis === 'x' ? 'crop_width' : 'crop_height';
            $shortName = $axis === 'x' ? 'crop_w' : 'crop_h';
            $size = "{$span->size->value}{$span->size->unit}";
            $hasCollision = false;
            if (array_key_exists($longName, $reservedKeys)) {
                $pairs[] = ["__ro_{$longName}", $size];
                $hasCollision = true;
            }
            if (array_key_exists($shortName, $reservedKeys)) {
                $pairs[] = ["__ro_{$shortName}", $size];
                $hasCollision = true;
            }
            if (!$hasCollision) {
                $pairs[] = [$longName, $size];
            }
        }
    }

    /**
     * @param array<string, true> $reservedKeys Template variable names.
     */
    private static function renderKey(string $name, array $reservedKeys): string
    {
        return array_key_exists($name, $reservedKeys) ? "__ro_{$name}" : $name;
    }

    private static function javascriptString(mixed $value): string
    {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_array($value)) {
            return implode(',', array_map(self::javascriptString(...), $value));
        }
        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        throw new InvalidArgumentException(
            sprintf('Value of type %s cannot be encoded in a signed URL', get_debug_type($value)),
        );
    }

    /**
     * @param list<array{0: string, 1: string}> $pairs
     */
    private static function formEncode(array $pairs): string
    {
        return implode(
            '&',
            array_map(
                static fn (array $pair): string => sprintf(
                    '%s=%s',
                    self::formEncodeComponent($pair[0]),
                    self::formEncodeComponent($pair[1]),
                ),
                $pairs,
            ),
        );
    }

    private static function formEncodeComponent(string $value): string
    {
        return str_replace(
            ['%20', '%2A', '~'],
            ['+', '*', '%7E'],
            rawurlencode($value),
        );
    }

    private static function pathEncodeComponent(string $value): string
    {
        return str_replace(
            ['%21', '%27', '%28', '%29', '%2A'],
            ['!', "'", '(', ')', '*'],
            rawurlencode($value),
        );
    }

    private function generateHmacToken(string $query): string
    {
        return hash_hmac('sha256', $query, $this->apiKey);
    }

    /** @throws JsonException */
    private static function encodeJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}
