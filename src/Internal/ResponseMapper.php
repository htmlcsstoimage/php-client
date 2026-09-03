<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateImageBatchSuccessResponse;
use HtmlCssToImage\Response\CreateImageSuccessResponse;
use HtmlCssToImage\Response\DeleteImageSuccessResponse;
use HtmlCssToImage\Response\ValidationError;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/** @internal */
final class ResponseMapper
{
    /** @throws UnexpectedValueException When a successful response is malformed. */
    public function image(
        ResponseInterface $response,
    ): CreateImageSuccessResponse|ApiErrorResponse {
        $data = self::decodeJson($response);
        if (self::isSuccessStatus($response)) {
            return self::imageFromData($data, 'image creation');
        }

        return self::error($response, $data);
    }

    /** @throws UnexpectedValueException When a successful response is malformed. */
    public function batch(
        ResponseInterface $response,
    ): CreateImageBatchSuccessResponse|ApiErrorResponse {
        $data = self::decodeJson($response);
        if (!self::isSuccessStatus($response)) {
            return self::error($response, $data);
        }

        $rawImages = $data['images'] ?? null;
        if (!is_array($rawImages) || !array_is_list($rawImages)) {
            throw new UnexpectedValueException(
                'A successful batch response must contain an images array.',
            );
        }

        $images = [];
        foreach ($rawImages as $index => $image) {
            if (!is_array($image)) {
                throw new UnexpectedValueException(
                    sprintf('Image %d in a successful batch response must be an object.', $index),
                );
            }
            $imageData = [];
            foreach ($image as $key => $value) {
                if (is_string($key)) {
                    $imageData[$key] = $value;
                }
            }
            $images[] = self::imageFromData($imageData, "batch image {$index}");
        }

        return new CreateImageBatchSuccessResponse($images);
    }

    public function delete(
        ResponseInterface $response,
    ): DeleteImageSuccessResponse|ApiErrorResponse {
        if (self::isSuccessStatus($response)) {
            return new DeleteImageSuccessResponse();
        }

        return self::error($response, self::decodeJson($response));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws UnexpectedValueException When required image fields are missing.
     */
    private static function imageFromData(
        array $data,
        string $context,
    ): CreateImageSuccessResponse {
        return new CreateImageSuccessResponse(
            id: self::requiredString($data, 'id', $context),
            url: self::requiredString($data, 'url', $context),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function error(
        ResponseInterface $response,
        array $data,
    ): ApiErrorResponse {
        $validationErrors = null;
        $rawValidationErrors = $data['validation_errors'] ?? null;
        if (is_array($rawValidationErrors)) {
            $validationErrors = [];
            foreach ($rawValidationErrors as $error) {
                if (!is_array($error)) {
                    continue;
                }
                $validationErrors[] = new ValidationError(
                    path: self::responseString($error['path'] ?? null),
                    message: self::responseString($error['message'] ?? null),
                );
            }
        }

        $message = $data['message'] ?? null;

        return new ApiErrorResponse(
            error: self::responseString(
                $data['error'] ?? null,
                'Unknown error',
            ),
            statusCode: $response->getStatusCode(),
            message: $message === null ? null : self::responseString($message),
            validationErrors: $validationErrors,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeJson(ResponseInterface $response): array
    {
        try {
            $decoded = json_decode(
                (string) $response->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
            if (is_array($decoded)) {
                $result = [];
                foreach ($decoded as $key => $value) {
                    if (is_string($key)) {
                        $result[$key] = $value;
                    }
                }

                return $result;
            }
        } catch (JsonException) {
            // Fall through to a typed API error or malformed-success failure.
        }

        return [
            'error' => 'Internal Server Error',
            'message' => sprintf(
                'The server returned an unexpected response (Status: %d).',
                $response->getStatusCode(),
            ),
        ];
    }

    private static function isSuccessStatus(ResponseInterface $response): bool
    {
        $status = $response->getStatusCode();

        return $status >= 200 && $status < 300;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws UnexpectedValueException
     */
    private static function requiredString(
        array $data,
        string $field,
        string $context,
    ): string {
        $value = $data[$field] ?? null;
        if (!is_string($value) || $value === '') {
            throw new UnexpectedValueException(
                sprintf(
                    'A successful %s response must contain a non-empty %s string.',
                    $context,
                    $field,
                ),
            );
        }

        return $value;
    }

    private static function responseString(
        mixed $value,
        string $default = '',
    ): string {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value) || is_bool($value)) {
            return self::scalarString($value);
        }

        return $default;
    }

    private static function scalarString(int|float|bool $value): string
    {
        return match ($value) {
            true => 'true',
            false => 'false',
            default => (string) $value,
        };
    }
}
