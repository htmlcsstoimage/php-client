<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use GuzzleHttp\Psr7\Request as HttpRequest;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/** @internal */
final readonly class HttpTransport
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $baseUrl,
        private string $authorizationHeader,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws JsonException When the request payload cannot be encoded.
     */
    public function post(string $path, array $payload): ResponseInterface
    {
        $request = new HttpRequest(
            'POST',
            "{$this->baseUrl}{$path}",
            [
                'Authorization' => $this->authorizationHeader,
                'Content-Type' => 'application/json',
                'User-Agent' => LibraryInfo::userAgent(),
            ],
            self::encodeJson($payload),
        );

        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param array<string, mixed>|null $payload Optional JSON body.
     *
     * @throws JsonException When the request payload cannot be encoded.
     */
    public function delete(
        string $path,
        ?array $payload = null,
    ): ResponseInterface {
        $headers = [
            'Authorization' => $this->authorizationHeader,
            'User-Agent' => LibraryInfo::userAgent(),
        ];
        $body = null;
        if ($payload !== null) {
            $headers['Content-Type'] = 'application/json';
            $body = self::encodeJson($payload);
        }

        $request = new HttpRequest(
            'DELETE',
            "{$this->baseUrl}{$path}",
            $headers,
            $body,
        );

        return $this->httpClient->sendRequest($request);
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
