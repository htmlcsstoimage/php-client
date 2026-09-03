# HTML/CSS to Image PHP Client

The official PHP client for the [HTML/CSS to Image API](https://htmlcsstoimage.com). It provides immutable request and response models, signed URL helpers, and an injectable PSR-18 HTTP transport.

This README documents how the client behaves. The central [API documentation](https://docs.htmlcsstoimage.com) is the source of truth for rendering features, parameter meanings, supported values, plan availability, and API limits. See the [parameter reference](https://docs.htmlcsstoimage.com/parameters/) when configuring a request.

## Installation

```bash
composer require html-css-to-image/client
```

PHP 8.2 or newer is required.

## Quick start

```php
<?php

use HtmlCssToImage\HtmlCssToImageClient;
use HtmlCssToImage\Request\CreateHtmlCssImageRequest;
use HtmlCssToImage\Response\CreateImageSuccessResponse;

require __DIR__ . '/vendor/autoload.php';

$client = new HtmlCssToImageClient(
    apiId: 'your-api-id',
    apiKey: 'your-api-key',
);

$result = $client->createImage(
    new CreateHtmlCssImageRequest(
        html: '<h1>Hello, world!</h1>',
        css: 'h1 { color: royalblue; }',
    ),
);

if ($result instanceof CreateImageSuccessResponse) {
    echo $result->id . ' ' . $result->url;
} else {
    echo $result->error;
}
```

Credentials are available in the [HCTI dashboard](https://htmlcsstoimage.com/dashboard). Keep the API key on a trusted server; never embed it in browser, desktop, or mobile application code.

### Environment credentials

Set `HCTI_API_ID` and `HCTI_API_KEY`, then use:

```php
$client = HtmlCssToImageClient::fromEnvironment();
```

`fromEnvironment()` throws `RuntimeException` when either variable is missing.

## Requests and responses

Request constructors use PHP-style `camelCase` named arguments. The client maps them to the API's `snake_case` fields. `null` fields are omitted from JSON payloads; an explicit `false` is preserved for normal POST requests.

| Request class | Use |
| --- | --- |
| `CreateHtmlCssImageRequest` | Create an image from HTML and CSS. |
| `CreateUrlImageRequest` | Capture a URL. |
| `CreateTemplatedImageRequest` | Render a saved template with values. |

All supported fields are documented by property types and PHPDoc. Their API behavior is documented in the [parameter reference](https://docs.htmlcsstoimage.com/parameters/), [URL screenshot guide](https://docs.htmlcsstoimage.com/getting-started/url-to-image/), and [template guide](https://docs.htmlcsstoimage.com/getting-started/templates/).

```php
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Request\CreateUrlImageRequest;
use HtmlCssToImage\ImageFormat;

$urlResult = $client->createImage(
    new CreateUrlImageRequest(
        url: 'https://example.com',
        viewportWidth: 1200,
        viewportHeight: 630,
        transparentBackground: true,
        format: ImageFormat::WEBP,
    ),
);

$templateResult = $client->createImage(
    new CreateTemplatedImageRequest(
        templateId: 'your-template-id',
        templateVersion: 3,
        templateValues: ['title' => 'Hello from PHP'],
    ),
);
```

`createImage()` returns either `CreateImageSuccessResponse` or `ApiErrorResponse`. Use `instanceof` to narrow the result to the appropriate response type. The `success` property remains available as a convenience. HTTP transport failures are raised by the PSR-18 client rather than converted into API responses, and a malformed successful API response raises `UnexpectedValueException`.

### Batch requests

```php
$result = $client->createImageBatch(
    variations: [
        new CreateHtmlCssImageRequest(css: 'h1 { color: crimson; }'),
        new CreateHtmlCssImageRequest(css: 'h1 { color: royalblue; }'),
    ],
    defaultOptions: new CreateHtmlCssImageRequest(
        html: '<h1>Shared HTML</h1>',
        viewportWidth: 600,
        viewportHeight: 315,
    ),
);
```

Only HTML/CSS and URL requests can be batched. Empty `html` or `url` values in variations are omitted so they can inherit from `defaultOptions`. An empty variation list returns a successful empty result without sending an HTTP request. Options unsupported by the batch API, such as `dedupeDurationS`, are not serialized. See the [batch API documentation](https://docs.htmlcsstoimage.com/getting-started/using-the-api/#batch-image-creation).

## Signed URLs

The signed URL helpers perform no network request. They create the exact query string, sign it with HMAC-SHA256 using the API key, and return a URL that can be shared without exposing that key.

```php
$templateUrl = $client->generateTemplatedImageUrlFromValues(
    'your-template-id',
    ['title' => 'Rendered on demand'],
    templateVersion: 2,
);

$renderUrl = $client->generateCreateAndRenderUrl(
    new CreateUrlImageRequest(
        url: 'https://example.com/card/42',
        viewportWidth: 1200,
        viewportHeight: 630,
    ),
);
```

Use `generateTemplatedImageUrl()` when you already have a complete `CreateTemplatedImageRequest`. Use `generateTemplatedImageUrlFromValues()` as a convenience when you have a template ID and values. Both methods accept `renderOptions`.

```php
use HtmlCssToImage\ImageFormat;
use HtmlCssToImage\Request\CreateTemplatedImageRequest;
use HtmlCssToImage\Render\RenderImageOptions;

$options = new RenderImageOptions(format: ImageFormat::WEBP, width: 1200, height: 630);

$templateUrl = $client->generateTemplatedImageUrl(
    new CreateTemplatedImageRequest(
        templateId: 'your-template-id',
        templateValues: ['title' => 'Rendered on demand'],
        templateVersion: 2,
    ),
    $options,
);
```

Client behavior worth knowing:

- Render options are added before signing, so the signature covers the final query string.
- When both a request and its render options specify a format, the render option controls the signed URL path.
- Template fields that collide with render-option query names are assigned the API's reserved `__ro_` names automatically.
- PDF layout options and deduplication options are omitted from create-and-render URLs because that endpoint does not support them.
- Custom URL headers become visible query parameters in a signed URL. Do not put secrets in them.

See the [signed URL documentation](https://docs.htmlcsstoimage.com/getting-started/create-and-render/) for endpoint behavior and security considerations.

## Image URLs and render options

`imageUrl()` builds a URL for an existing image without making a request:

```php
$url = $client->imageUrl(
    'image-id',
    new RenderImageOptions(format: ImageFormat::JPG, dpi: 96, width: 1200),
);
```

Cropping uses immutable value objects and explicit factory methods:

```php
use HtmlCssToImage\Render\RenderImageCrop;
use HtmlCssToImage\Render\RenderImageCropPosition;
use HtmlCssToImage\Render\RenderImageCropSpan;

$crop = RenderImageCrop::rectangle(
    horizontal: RenderImageCropSpan::between(
        RenderImageCropPosition::percent(10),
        RenderImageCropPosition::percent(90),
    ),
);

$url = $client->imageUrl('image-id', new RenderImageOptions(crop: $crop));
```

The crop factories validate their inputs before generating a URL. Refer to the [image URL and cropping documentation](https://docs.htmlcsstoimage.com/getting-started/using-the-api/#cropping-parameters) for transformation semantics and limits.

## Deleting images

```php
$single = $client->deleteImage('image-id');
$batch = $client->deleteImageBatch(['image-id-1', 'image-id-2']);
```

Every successful `2xx` response maps to `DeleteImageSuccessResponse`. API errors use `ApiErrorResponse`, while network failures remain PSR-18 exceptions.

## HTTP configuration

The default transport is a persistent Guzzle client with a 30-second timeout and no automatic retries. Inject any PSR-18 `ClientInterface` to configure timeouts, retries, proxies, certificates, connection limits, or test behavior:

```php
use GuzzleHttp\Client as GuzzleClient;

$httpClient = new GuzzleClient([
    'timeout' => 90,
    'http_errors' => false,
]);

$client = new HtmlCssToImageClient(
    apiId: 'your-api-id',
    apiKey: 'your-api-key',
    httpClient: $httpClient,
);
```

The injected HTTP client remains caller-owned and is never closed or reconfigured by this package. Every API request includes `HCTIPHP/<version>` as its `User-Agent`, including requests sent through an injected client. Retry policy intentionally belongs to the application and can be added through the selected PSR-18 client's middleware or handler system.

## Error handling

```php
use Psr\Http\Client\NetworkExceptionInterface;
use HtmlCssToImage\Response\ApiErrorResponse;

try {
    $result = $client->createImage($request);
} catch (NetworkExceptionInterface $exception) {
    // Apply application-specific policy.
}

if (isset($result) && $result instanceof ApiErrorResponse) {
    echo "HTTP {$result->statusCode}: {$result->error}";
    foreach ($result->validationErrors ?? [] as $error) {
        echo "{$error->path}: {$error->message}";
    }
}
```

## Client API

| Method | Returns | Interaction |
| --- | --- | --- |
| `fromEnvironment(...)` | `HtmlCssToImageClient` | Reads credentials from the environment. |
| `createImage($request)` | success or error response | Sends `POST /v1/image`. |
| `createImageBatch($variations, $defaultOptions = null)` | batch success or error response | Sends `POST /v1/image/batch`, unless the list is empty. |
| `deleteImage($imageId)` | delete success or error response | Sends `DELETE /v1/image/{id}`. |
| `deleteImageBatch($imageIds)` | delete success or error response | Sends `DELETE /v1/image/batch`. |
| `imageUrl($imageId, $renderOptions = null)` | `string` | Builds an existing-image URL locally. |
| `generateTemplatedImageUrl($request, $renderOptions = null)` | `string` | Builds and signs a template URL from a request object locally. |
| `generateTemplatedImageUrlFromValues(...)` | `string` | Builds and signs a template URL from an ID and values locally. |
| `generateCreateAndRenderUrl(...)` | `string` | Builds and signs a URL screenshot locally. |

`HtmlCssToImageClient` implements `HtmlCssToImageClientInterface`. Public request, response, PDF, and render/crop classes include PHPDoc for their constructors, properties, parameters, return types, exceptions, and IDE help.

## Development

```bash
composer install
composer validate --strict
composer analyse
composer test
```

## License

MIT
