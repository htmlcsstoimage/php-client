<?php

declare(strict_types=1);

namespace HtmlCssToImage\Tests;

use GuzzleHttp\Psr7\Response;
use HtmlCssToImage\HtmlCssToImageClient;
use HtmlCssToImage\ImageFormat;
use HtmlCssToImage\Request\CreateHtmlCssImageRequest;
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\Request\PDFMargins;
use HtmlCssToImage\Request\PDFOptions;
use HtmlCssToImage\Request\PDFValueWithUnits;
use HtmlCssToImage\Render\RenderImageAspectRatio;
use HtmlCssToImage\Render\RenderImageCrop;
use HtmlCssToImage\Render\RenderImageCropPosition;
use HtmlCssToImage\Render\RenderImageCropSize;
use HtmlCssToImage\Render\RenderImageCropSpan;
use HtmlCssToImage\Render\RenderImageOptions;
use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateImageBatchSuccessResponse;
use HtmlCssToImage\Response\CreateImageSuccessResponse;
use HtmlCssToImage\Response\DeleteImageSuccessResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

final class HtmlCssToImageClientTest extends TestCase
{
    private const API_ID = 'user_id';

    private const API_KEY = 'api_key';

    public function testCreateImageMapsHtmlFontsPdfAndAuthorization(): void
    {
        $http = new QueueHttpClient([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"id":"123","url":"https://hcti.io/v1/image/123"}',
            ),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(
            new CreateHtmlCssImageRequest(
                html: '<h1>Test</h1>',
                googleFonts: ['Roboto', 'Open Sans', 'Open Sans'],
                pdfOptions: new PDFOptions(
                    margins: new PDFMargins(
                        top: 10,
                        right: 20,
                        bottom: new PDFValueWithUnits(5, 'mm'),
                        left: new PDFValueWithUnits(20, 'in'),
                    ),
                ),
                format: ImageFormat::WEBP,
            ),
        );

        self::assertInstanceOf(CreateImageSuccessResponse::class, $result);
        self::assertSame('123', $result->id);
        self::assertCount(1, $http->requests);
        $request = $http->requests[0];
        self::assertSame('https://hcti.io/v1/image', (string) $request->getUri());
        self::assertSame(
            'Basic dXNlcl9pZDphcGlfa2V5',
            $request->getHeaderLine('Authorization'),
        );
        $payload = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('<h1>Test</h1>', $payload['html']);
        self::assertSame('Roboto|Open+Sans', $payload['google_fonts']);
        self::assertSame('webp', $payload['format']);
        self::assertSame(
            ['10px', '20px', '5mm', '20in'],
            $payload['pdf_options']['margins'],
        );
    }

    public function testCreateImageMapsTemplateWithoutInternalFields(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"id":"123","url":"image-url"}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(
            new CreateTemplatedImageRequest(
                templateId: 'template-id',
                templateValues: ['title' => 'Hello'],
                format: ImageFormat::PDF,
            ),
        );

        self::assertTrue($result->success);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertSame([
            'template_id' => 'template-id',
            'template_values' => ['title' => 'Hello'],
            'format' => 'pdf',
        ], $payload);
    }

    public function testCreateImageMapsUrlCssAndFalseValues(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"id":"123","url":"image-url"}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(
            new CreateUrlImageRequest(
                url: 'https://example.com',
                css: 'body { background: black; }',
                fullScreen: false,
            ),
        );

        self::assertTrue($result->success);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertSame('https://example.com', $payload['url']);
        self::assertSame('body { background: black; }', $payload['css']);
        self::assertFalse($payload['full_screen']);
    }

    public function testCreateImageMapsNewRenderAndHeaderOptions(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"id":"123","url":"image-url"}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(
            new CreateUrlImageRequest(
                url: 'https://example.com/private',
                dedupeDurationS: 300,
                storageDestinationId: 'storage-1',
                transparentBackground: false,
                headers: [
                    'Authorization' => 'Bearer secret',
                    'X-Card' => '42',
                ],
                additionalHeaderOrigins: ['https://assets.example.com'],
                includeHeadersOnSubrequests: true,
                identifyAsHcti: true,
            ),
        );

        self::assertTrue($result->success);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertSame(300, $payload['dedupe_duration_s']);
        self::assertSame('storage-1', $payload['storage_destination_id']);
        self::assertFalse($payload['transparent_background']);
        self::assertSame(
            ['Authorization' => 'Bearer secret', 'X-Card' => '42'],
            $payload['headers'],
        );
        self::assertSame(
            ['https://assets.example.com'],
            $payload['additional_header_origins'],
        );
        self::assertTrue($payload['include_headers_on_subrequests']);
        self::assertTrue($payload['identify_as_hcti']);
    }

    public function testBatchOmitsEmptyInheritedContent(): void
    {
        $http = new QueueHttpClient([
            new Response(
                200,
                [],
                '{"images":[{"id":"1","url":"u1"},{"id":"2","url":"u2"}]}',
            ),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImageBatch(
            [
                new CreateHtmlCssImageRequest(html: '<h1>V1</h1>'),
                new CreateHtmlCssImageRequest(
                    viewportWidth: 600,
                    format: ImageFormat::JPG,
                ),
            ],
            new CreateHtmlCssImageRequest(
                html: '<h1>BASE</h1>',
                viewportWidth: 1280,
                format: ImageFormat::WEBP,
            ),
        );

        self::assertInstanceOf(CreateImageBatchSuccessResponse::class, $result);
        self::assertCount(2, $result->images);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertArrayNotHasKey('html', $payload['variations'][1]);
        self::assertSame('jpg', $payload['variations'][1]['format']);
        self::assertSame('<h1>BASE</h1>', $payload['default_options']['html']);
        self::assertSame('webp', $payload['default_options']['format']);
    }

    public function testEmptyBatchDoesNotSendRequest(): void
    {
        $http = new QueueHttpClient([]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImageBatch([]);

        self::assertTrue($result->success);
        self::assertSame([], $result->images);
        self::assertSame([], $http->requests);
    }

    public function testBatchOmitsPostOnlyDedupeOption(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"images":[]}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImageBatch(
            [
                new CreateHtmlCssImageRequest(
                    html: '<h1>V1</h1>',
                    dedupeDurationS: 300,
                    transparentBackground: true,
                ),
            ],
            new CreateHtmlCssImageRequest(
                html: '<h1>BASE</h1>',
                dedupeDurationS: 300,
            ),
        );

        self::assertTrue($result->success);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertArrayNotHasKey('dedupe_duration_s', $payload['variations'][0]);
        self::assertArrayNotHasKey('dedupe_duration_s', $payload['default_options']);
        self::assertTrue($payload['variations'][0]['transparent_background']);
    }

    public function testBatchWithoutDefaultsOmitsDefaultOptions(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"images":[]}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImageBatch([
            new CreateHtmlCssImageRequest(html: '<h1>Only</h1>'),
        ]);

        self::assertTrue($result->success);
        $payload = json_decode(
            (string) $http->requests[0]->getBody(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        self::assertArrayNotHasKey('default_options', $payload);
    }

    public function testTemplatedUrlUsesWhatwgEncodingAndValidHmac(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $url = $client->generateTemplatedImageUrl(
            'my-template',
            [
                'title' => 'Hello world~*',
                'data' => ['enabled' => true],
                'ignored' => null,
            ],
            2,
        );

        $query = (string) parse_url($url, PHP_URL_QUERY);
        $path = (string) parse_url($url, PHP_URL_PATH);
        $token = basename($path);

        self::assertStringContainsString('title=Hello+world%7E*', $query);
        self::assertSame(
            hash_hmac('sha256', $query, self::API_KEY),
            $token,
        );
    }

    public function testCreateAndRenderUrlOmitsFalseAndPdfOptions(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $url = $client->generateCreateAndRenderUrl(
            new CreateUrlImageRequest(
                url: 'https://example.com/a path',
                css: 'body { color: red; }',
                fullScreen: true,
                blockConsentBanners: false,
                pdfOptions: new PDFOptions(printBackground: true),
                format: ImageFormat::PDF,
            ),
        );

        parse_str((string) parse_url($url, PHP_URL_QUERY), $values);
        $path = (string) parse_url($url, PHP_URL_PATH);
        self::assertSame('https://example.com/a path', $values['url']);
        self::assertSame('true', $values['full_screen']);
        self::assertArrayNotHasKey('block_consent_banners', $values);
        self::assertArrayNotHasKey('pdf_options', $values);
        self::assertArrayNotHasKey('format', $values);
        self::assertStringEndsWith('/pdf', $path);
    }

    public function testCreateAndRenderSupportsHeadersAndRenderOptions(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $url = $client->generateCreateAndRenderUrl(
            new CreateUrlImageRequest(
                url: 'https://example.com/private',
                format: ImageFormat::PDF,
                dedupeDurationS: 300,
                transparentBackground: false,
                headers: [
                    'Authorization' => 'Bearer secret',
                    'X-Test' => 'yes',
                ],
                additionalHeaderOrigins: [
                    'https://assets.example.com',
                    'https://fonts.example.com',
                ],
                includeHeadersOnSubrequests: true,
                identifyAsHcti: true,
            ),
            new RenderImageOptions(
                format: ImageFormat::WEBP,
                width: 1200,
                height: 630,
            ),
        );

        $query = (string) parse_url($url, PHP_URL_QUERY);
        $path = (string) parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $token = $segments[count($segments) - 2];

        self::assertSame(
            ['Authorization:Bearer secret', 'X-Test:yes'],
            self::queryValues($query, 'headers'),
        );
        self::assertSame(
            ['https://assets.example.com', 'https://fonts.example.com'],
            self::queryValues($query, 'additional_header_origins'),
        );
        self::assertSame(['false'], self::queryValues($query, 'transparent_background'));
        self::assertSame(['1200'], self::queryValues($query, 'width'));
        self::assertSame(['630'], self::queryValues($query, 'height'));
        self::assertSame([], self::queryValues($query, 'dedupe_duration_s'));
        self::assertStringEndsWith('/webp', $path);
        self::assertSame(hash_hmac('sha256', $query, self::API_KEY), $token);
    }

    public function testImageUrlSupportsResizeDpiAndCrop(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $crop = RenderImageCrop::aspectRatioFromWidth(
            new RenderImageAspectRatio(16, 9),
            RenderImageCropSpan::between(
                RenderImageCropPosition::percent(10),
                RenderImageCropPosition::percent(90),
            ),
            heightOrigin: 'center',
        );

        $url = $client->imageUrl(
            'folder/image id',
            new RenderImageOptions(
                format: ImageFormat::JPG,
                dpi: 96,
                width: 600,
                crop: $crop,
            ),
        );

        self::assertSame(
            'https://hcti.io/v1/image/folder%2Fimage%20id.jpg?'
            . 'dpi=96&width=600&aspect_ratio=16_9&y_origin=center&'
            . 'x_1=10%25&x_2=90%25',
            $url,
        );
    }

    public function testTemplateRenderOptionsAvoidValueCollisions(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $url = $client->generateTemplatedImageUrl(
            'template-id',
            [
                'width' => 'template width',
                'crop_width' => 'template long crop',
                'crop_w' => 'template short crop',
            ],
            renderOptions: new RenderImageOptions(
                format: ImageFormat::PNG,
                width: 1200,
                crop: RenderImageCrop::rectangle(
                    horizontal: RenderImageCropSpan::sized(
                        RenderImageCropSize::pixels(100),
                        'end',
                    ),
                ),
            ),
        );

        $query = (string) parse_url($url, PHP_URL_QUERY);
        $path = (string) parse_url($url, PHP_URL_PATH);
        self::assertSame(['template width'], self::queryValues($query, 'width'));
        self::assertSame(['1200'], self::queryValues($query, '__ro_width'));
        self::assertSame(['100px'], self::queryValues($query, '__ro_crop_width'));
        self::assertSame(['100px'], self::queryValues($query, '__ro_crop_w'));
        self::assertSame(['end'], self::queryValues($query, 'x_origin'));
        self::assertStringEndsWith('/png', $path);
    }

    public function testRenderOptionsValidateRanges(): void
    {
        try {
            new RenderImageOptions(dpi: 30);
            self::fail('DPI 30 should be rejected');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('DPI', $exception->getMessage());
        }

        try {
            new RenderImageOptions(width: 5001);
            self::fail('Width 5001 should be rejected');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('width', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than');
        RenderImageCropSpan::between(
            RenderImageCropPosition::pixels(20),
            RenderImageCropPosition::pixels(10),
        );
    }

    public function testDeleteImageAndBatch(): void
    {
        $http = new QueueHttpClient([
            new Response(204),
            new Response(204),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $single = $client->deleteImage('folder/image id');
        $batch = $client->deleteImageBatch(['one', 'two']);

        self::assertInstanceOf(DeleteImageSuccessResponse::class, $single);
        self::assertTrue($batch->success);
        self::assertSame('DELETE', $http->requests[0]->getMethod());
        self::assertSame(
            'https://hcti.io/v1/image/folder%2Fimage%20id',
            (string) $http->requests[0]->getUri(),
        );
        self::assertSame(
            ['ids' => ['one', 'two']],
            json_decode(
                (string) $http->requests[1]->getBody(),
                true,
                flags: JSON_THROW_ON_ERROR,
            ),
        );
    }

    public function testDeleteErrorIsTyped(): void
    {
        $http = new QueueHttpClient([
            new Response(404, [], '{"error":"Not Found","message":"No image"}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->deleteImage('missing');

        self::assertInstanceOf(ApiErrorResponse::class, $result);
        self::assertFalse($result->success);
        self::assertSame('Not Found', $result->error);
        self::assertSame(404, $result->statusCode);
    }

    public function testValidationErrorIsTyped(): void
    {
        $http = new QueueHttpClient([
            new Response(
                400,
                [],
                <<<'JSON'
                {
                  "error": "Validation Failed",
                  "message": "Invalid input",
                  "validation_errors": [
                    {"path": "html", "message": "is required"}
                  ]
                }
                JSON,
            ),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(new CreateHtmlCssImageRequest());

        self::assertInstanceOf(ApiErrorResponse::class, $result);
        self::assertFalse($result->success);
        self::assertSame('Validation Failed', $result->error);
        self::assertSame('html', $result->validationErrors[0]->path);
    }

    public function testMalformedErrorHasFallbackDetails(): void
    {
        $http = new QueueHttpClient([
            new Response(500, [], '<html>edge error</html>'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $result = $client->createImage(
            new CreateUrlImageRequest(url: 'https://example.com'),
        );

        self::assertFalse($result->success);
        self::assertSame('Internal Server Error', $result->error);
        self::assertStringContainsString('Status: 500', $result->message);
        self::assertSame(500, $result->statusCode);
    }

    public function testMalformedSuccessfulImageResponseThrows(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"url":"image-url"}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('non-empty id string');
        $client->createImage(new CreateHtmlCssImageRequest(html: 'ok'));
    }

    public function testMalformedSuccessfulBatchResponseThrows(): void
    {
        $http = new QueueHttpClient([
            new Response(200, [], '{"images":[{"id":"1"}]}'),
        ]);
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY, $http);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('non-empty url string');
        $client->createImageBatch([
            new CreateHtmlCssImageRequest(html: 'ok'),
        ]);
    }

    public function testTemplateRequestFormatControlsSignedUrl(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);
        $url = $client->generateTemplatedImageUrl(
            new CreateTemplatedImageRequest(
                templateId: 'template-id',
                templateValues: ['title' => 'Hello'],
                format: ImageFormat::PDF,
            ),
        );

        self::assertStringEndsWith('/pdf?title=Hello', $url);
    }

    public function testPdfRenderFormatIsSupported(): void
    {
        $client = new HtmlCssToImageClient(self::API_ID, self::API_KEY);

        self::assertSame(
            'https://hcti.io/v1/image/image-id.pdf',
            $client->imageUrl(
                'image-id',
                new RenderImageOptions(format: ImageFormat::PDF),
            ),
        );
    }

    public function testFromEnvironment(): void
    {
        $previousId = getenv('HCTI_API_ID');
        $previousKey = getenv('HCTI_API_KEY');
        putenv('HCTI_API_ID=env-id');
        putenv('HCTI_API_KEY=env-key');

        try {
            $http = new QueueHttpClient([
                new Response(200, [], '{"id":"1","url":"u1"}'),
            ]);
            $client = HtmlCssToImageClient::fromEnvironment($http);
            $result = $client->createImage(
                new CreateHtmlCssImageRequest(html: 'ok'),
            );
            self::assertTrue($result->success);
        } finally {
            self::restoreEnvironment('HCTI_API_ID', $previousId);
            self::restoreEnvironment('HCTI_API_KEY', $previousKey);
        }
    }

    public function testFromEnvironmentRequiresBothValues(): void
    {
        $previousId = getenv('HCTI_API_ID');
        $previousKey = getenv('HCTI_API_KEY');
        putenv('HCTI_API_ID');
        putenv('HCTI_API_KEY');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('HCTI_API_ID');
            HtmlCssToImageClient::fromEnvironment();
        } finally {
            self::restoreEnvironment('HCTI_API_ID', $previousId);
            self::restoreEnvironment('HCTI_API_KEY', $previousKey);
        }
    }

    private static function restoreEnvironment(
        string $name,
        string|false $value,
    ): void {
        putenv($value === false ? $name : "{$name}={$value}");
    }

    /** @return list<string> */
    private static function queryValues(string $query, string $name): array
    {
        $values = [];
        foreach (explode('&', $query) as $pair) {
            [$encodedName, $encodedValue] = array_pad(explode('=', $pair, 2), 2, '');
            if (urldecode($encodedName) === $name) {
                $values[] = urldecode($encodedValue);
            }
        }

        return $values;
    }
}
