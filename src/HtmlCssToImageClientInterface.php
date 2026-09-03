<?php

declare(strict_types=1);

namespace HtmlCssToImage;

use HtmlCssToImage\Request\BatchCreateImageRequest;
use HtmlCssToImage\Request\CreateImageRequest;
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\Render\RenderImageOptions;
use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateImageBatchSuccessResponse;
use HtmlCssToImage\Response\CreateImageSuccessResponse;
use HtmlCssToImage\Response\DeleteImageSuccessResponse;
use Psr\Http\Client\ClientInterface;

/**
 * Public contract for the official HTML/CSS to Image PHP client.
 *
 * @api
 */
interface HtmlCssToImageClientInterface
{
    /**
     * Create a client from HCTI_API_ID and HCTI_API_KEY.
     *
     * @param ClientInterface|null $httpClient Optional caller-owned PSR-18
     *        client. When null, a default Guzzle client is created.
     * @param string $baseUrl API origin; primarily useful for tests/proxies.
     *
     * @throws \RuntimeException When either environment variable is missing.
     */
    public static function fromEnvironment(
        ?ClientInterface $httpClient = null,
        string $baseUrl = 'https://hcti.io',
    ): self;

    /**
     * Create one image from HTML/CSS, a URL, or a saved template.
     *
     * API 4xx and 5xx responses are represented by
     * ApiErrorResponse. PSR-18 transport exceptions are not wrapped.
     *
     * @param CreateImageRequest $request Content and rendering options.
     *
     * @return CreateImageSuccessResponse|ApiErrorResponse
     *
     * @throws \JsonException When the request cannot be JSON encoded.
     * @throws \UnexpectedValueException When a successful response is malformed.
     */
    public function createImage(
        CreateImageRequest $request,
    ): CreateImageSuccessResponse|ApiErrorResponse;

    /**
     * Create several HTML/CSS or URL images in one API call.
     *
     * Empty HTML or URL values are omitted so variations can inherit content
     * from default options. An empty variation array returns a successful
     * empty response without sending an HTTP request.
     *
     * @param list<BatchCreateImageRequest> $variations Per-image values.
     * @param BatchCreateImageRequest|null $defaultOptions Shared defaults.
     *
     * @return CreateImageBatchSuccessResponse|ApiErrorResponse
     *
     * @throws \JsonException When the request cannot be JSON encoded.
     * @throws \UnexpectedValueException When a successful response is malformed.
     */
    public function createImageBatch(
        array $variations,
        ?BatchCreateImageRequest $defaultOptions = null,
    ): CreateImageBatchSuccessResponse|ApiErrorResponse;

    /**
     * Delete one generated image.
     *
     * @param string $imageId HCTI image identifier.
     *
     * @return DeleteImageSuccessResponse|ApiErrorResponse
     */
    public function deleteImage(
        string $imageId,
    ): DeleteImageSuccessResponse|ApiErrorResponse;

    /**
     * Delete several generated images in one API call.
     *
     * @param list<string> $imageIds HCTI image identifiers to delete.
     *
     * @return DeleteImageSuccessResponse|ApiErrorResponse
     *
     * @throws \JsonException When the request cannot be JSON encoded.
     */
    public function deleteImageBatch(
        array $imageIds,
    ): DeleteImageSuccessResponse|ApiErrorResponse;

    /**
     * Build a URL for retrieving an existing image.
     *
     * @param string $imageId HCTI image identifier.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings. Cropping occurs before resizing.
     *
     * @return string Image URL. This method performs no HTTP request.
     */
    public function imageUrl(
        string $imageId,
        ?RenderImageOptions $renderOptions = null,
    ): string;

    /**
     * Generate a signed on-demand URL for a saved template.
     *
     * This method performs no HTTP request.
     *
     * @param CreateTemplatedImageRequest $request Complete template request.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature. Its format
     *        takes precedence over the request's format.
     *
     * @return string Signed image URL safe to expose to a frontend.
     *
     * @throws \JsonException When a template value cannot be JSON encoded.
     */
    public function generateTemplatedImageUrl(
        CreateTemplatedImageRequest $request,
        ?RenderImageOptions $renderOptions = null,
    ): string;

    /**
     * Generate a signed on-demand URL from a template ID and values.
     *
     * This convenience method performs no HTTP request.
     *
     * @param string $templateId Identifier of the saved template.
     * @param array<string, mixed> $templateValues Template variable values.
     * @param int|null $templateVersion Specific version, or latest when null.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature.
     *
     * @return string Signed image URL safe to expose to a frontend.
     *
     * @throws \JsonException When a template value cannot be JSON encoded.
     */
    public function generateTemplatedImageUrlFromValues(
        string $templateId,
        array $templateValues = [],
        ?int $templateVersion = null,
        ?RenderImageOptions $renderOptions = null,
    ): string;

    /**
     * Generate a signed create-and-render URL for a URL screenshot.
     *
     * This method performs no HTTP request. PDF layout options are not
     * supported by this endpoint and are omitted.
     *
     * @param CreateUrlImageRequest $request URL and screenshot options.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature. Its format
     *        takes precedence over the request's format.
     *
     * @return string Signed image URL safe to expose to a frontend.
     */
    public function generateCreateAndRenderUrl(
        CreateUrlImageRequest $request,
        ?RenderImageOptions $renderOptions = null,
    ): string;
}
