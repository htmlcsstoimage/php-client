<?php

declare(strict_types=1);

namespace HtmlCssToImage;

use GuzzleHttp\Client as GuzzleClient;
use HtmlCssToImage\Internal\HttpTransport;
use HtmlCssToImage\Internal\RequestMapper;
use HtmlCssToImage\Internal\ResponseMapper;
use HtmlCssToImage\Internal\UrlBuilder;
use HtmlCssToImage\Request\BatchCreateImageRequest;
use HtmlCssToImage\Request\CreateImageRequest;
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\Render\RenderImageOptions;
use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateImageBatchSuccessResponse;
use HtmlCssToImage\Response\CreateImageSuccessResponse;
use HtmlCssToImage\Response\DeleteImageSuccessResponse;
use JsonException;
use Psr\Http\Client\ClientInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * Official PHP client for the HTML/CSS to Image API.
 *
 * This class performs no automatic retries. Inject a configured PSR-18 client
 * to control retry middleware, timeouts, proxies, certificates, or testing.
 * Injected clients remain owned by the caller.
 *
 * @api
 */
final class HtmlCssToImageClient implements HtmlCssToImageClientInterface
{
    private readonly HttpTransport $transport;

    private readonly RequestMapper $requestMapper;

    private readonly ResponseMapper $responseMapper;

    private readonly UrlBuilder $urlBuilder;

    /**
     * @param string $apiId HCTI API ID from the dashboard.
     * @param string $apiKey HCTI API key from the dashboard.
     * @param ClientInterface|null $httpClient Optional caller-owned PSR-18
     *        client. A no-retry Guzzle client is created when omitted.
     * @param string $baseUrl API origin; primarily useful for tests/proxies.
     */
    public function __construct(
        string $apiId,
        string $apiKey,
        ?ClientInterface $httpClient = null,
        string $baseUrl = 'https://hcti.io',
    ) {
        $client = $httpClient ?? new GuzzleClient([
            'timeout' => 30,
            'http_errors' => false,
        ]);
        $apiBaseUrl = rtrim($baseUrl, '/');
        $credentials = base64_encode("{$apiId}:{$apiKey}");
        $authorizationHeader = "Basic {$credentials}";

        $this->requestMapper = new RequestMapper();
        $this->responseMapper = new ResponseMapper();
        $this->transport = new HttpTransport(
            $client,
            $apiBaseUrl,
            $authorizationHeader,
        );
        $this->urlBuilder = new UrlBuilder(
            $apiBaseUrl,
            $apiId,
            $apiKey,
            $this->requestMapper,
        );
    }

    /**
     * Create a client from HCTI_API_ID and HCTI_API_KEY.
     *
     * @param ClientInterface|null $httpClient Optional caller-owned client.
     * @param string $baseUrl API origin; primarily useful for tests/proxies.
     *
     * @throws RuntimeException When either environment variable is missing.
     */
    public static function fromEnvironment(
        ?ClientInterface $httpClient = null,
        string $baseUrl = 'https://hcti.io',
    ): self {
        $apiId = getenv('HCTI_API_ID');
        $apiKey = getenv('HCTI_API_KEY');

        if (!is_string($apiId) || $apiId === '' || !is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException(
                'Missing environment variables HCTI_API_ID or HCTI_API_KEY',
            );
        }

        return new self($apiId, $apiKey, $httpClient, $baseUrl);
    }

    /**
     * Create one image from HTML/CSS, a URL, or a saved template.
     *
     * @param CreateImageRequest $request Content and rendering options.
     *
     * @return CreateImageSuccessResponse|ApiErrorResponse
     *
     * @throws JsonException When the request cannot be JSON encoded.
     * @throws UnexpectedValueException When a successful response is malformed.
     */
    public function createImage(
        CreateImageRequest $request,
    ): CreateImageSuccessResponse|ApiErrorResponse {
        $response = $this->transport->post(
            '/v1/image',
            $this->requestMapper->map($request),
        );

        return $this->responseMapper->image($response);
    }

    /**
     * Create several HTML/CSS or URL images in one API call.
     *
     * @param list<BatchCreateImageRequest> $variations Per-image values.
     * @param BatchCreateImageRequest|null $defaultOptions Shared defaults.
     *
     * @return CreateImageBatchSuccessResponse|ApiErrorResponse
     *
     * @throws JsonException When the request cannot be JSON encoded.
     * @throws UnexpectedValueException When a successful response is malformed.
     */
    public function createImageBatch(
        array $variations,
        ?BatchCreateImageRequest $defaultOptions = null,
    ): CreateImageBatchSuccessResponse|ApiErrorResponse {
        if ($variations === []) {
            return new CreateImageBatchSuccessResponse([]);
        }

        $payload = [
            'variations' => array_map(
                fn (BatchCreateImageRequest $request): array => $this->requestMapper->map(
                    $request,
                    true,
                ),
                $variations,
            ),
        ];

        if ($defaultOptions !== null) {
            $payload['default_options'] = $this->requestMapper->map(
                $defaultOptions,
                true,
            );
        }

        $response = $this->transport->post('/v1/image/batch', $payload);

        return $this->responseMapper->batch($response);
    }

    /**
     * Delete one generated image.
     *
     * @param string $imageId HCTI image identifier.
     *
     * @return DeleteImageSuccessResponse|ApiErrorResponse
     */
    public function deleteImage(
        string $imageId,
    ): DeleteImageSuccessResponse|ApiErrorResponse {
        $response = $this->transport->delete(
            '/v1/image/' . self::pathEncodeComponent($imageId),
        );

        return $this->responseMapper->delete($response);
    }

    /**
     * Delete several generated images in one API call.
     *
     * @param list<string> $imageIds HCTI image identifiers to delete.
     *
     * @return DeleteImageSuccessResponse|ApiErrorResponse
     *
     * @throws JsonException When the request cannot be JSON encoded.
     */
    public function deleteImageBatch(
        array $imageIds,
    ): DeleteImageSuccessResponse|ApiErrorResponse {
        $response = $this->transport->delete(
            '/v1/image/batch',
            ['ids' => $imageIds],
        );

        return $this->responseMapper->delete($response);
    }

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
    ): string {
        return $this->urlBuilder->image($imageId, $renderOptions);
    }

    /**
     * Generate a signed on-demand URL for a saved template.
     *
     * This method performs no HTTP request. Arrays and objects are encoded as
     * compact JSON before the query string is signed.
     *
     * @param CreateTemplatedImageRequest $request Complete template request.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature. Its format
     *        takes precedence over the request's format.
     *
     * @return string Signed URL safe to expose to a frontend.
     *
     * @throws JsonException When a template value cannot be JSON encoded.
     */
    public function generateTemplatedImageUrl(
        CreateTemplatedImageRequest $request,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        return $this->urlBuilder->templated($request, $renderOptions);
    }

    /**
     * Generate a signed on-demand URL from a template ID and values.
     *
     * This convenience method performs no HTTP request. Arrays and objects are
     * encoded as compact JSON before the query string is signed.
     *
     * @param string $templateId Identifier of the saved template.
     * @param array<string, mixed> $templateValues Template variable values.
     * @param int|null $templateVersion Specific version, or latest when null.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature.
     *
     * @return string Signed URL safe to expose to a frontend.
     *
     * @throws JsonException When a template value cannot be JSON encoded.
     */
    public function generateTemplatedImageUrlFromValues(
        string $templateId,
        array $templateValues = [],
        ?int $templateVersion = null,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        return $this->generateTemplatedImageUrl(
            new CreateTemplatedImageRequest(
                templateId: $templateId,
                templateValues: $templateValues,
                templateVersion: $templateVersion,
            ),
            $renderOptions,
        );
    }

    /**
     * Generate a signed create-and-render URL for a URL screenshot.
     *
     * This method performs no HTTP request. PDF layout and deduplication
     * options are omitted. False booleans are omitted except
     * transparentBackground, whose explicit false value is meaningful.
     *
     * @param CreateUrlImageRequest $request URL and screenshot options.
     * @param RenderImageOptions|null $renderOptions Optional format, DPI,
     *        resize, and crop settings included in the signature. Its format
     *        takes precedence over the request's format.
     *
     * @return string Signed URL safe to expose to a frontend.
     */
    public function generateCreateAndRenderUrl(
        CreateUrlImageRequest $request,
        ?RenderImageOptions $renderOptions = null,
    ): string {
        return $this->urlBuilder->createAndRender($request, $renderOptions);
    }

    private static function pathEncodeComponent(string $value): string
    {
        return str_replace(
            ['%21', '%27', '%28', '%29', '%2A'],
            ['!', "'", '(', ')', '*'],
            rawurlencode($value),
        );
    }
}
