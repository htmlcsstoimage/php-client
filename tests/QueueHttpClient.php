<?php

declare(strict_types=1);

namespace HtmlCssToImage\Tests;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Minimal in-memory PSR-18 client used by the unit tests.
 */
final class QueueHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /**
     * @param list<ResponseInterface> $responses
     */
    public function __construct(
        private array $responses,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $response = array_shift($this->responses);
        if (!$response instanceof ResponseInterface) {
            throw new RuntimeException('No queued response');
        }

        return $response;
    }
}
