<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use HtmlCssToImage\Recording\RecordingError;
use HtmlCssToImage\Recording\RecordingStatus;
use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateImageBatchSuccessResponse;
use HtmlCssToImage\Response\CreateImageSuccessResponse;
use HtmlCssToImage\Response\CreateRecordingSuccessResponse;
use HtmlCssToImage\Response\DeleteImageSuccessResponse;
use HtmlCssToImage\Response\DeleteRecordingSuccessResponse;
use HtmlCssToImage\Response\RecordingStatusSuccessResponse;
use HtmlCssToImage\Response\ValidationError;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/** @internal */
final class ResponseMapper
{
    public function recording(ResponseInterface $response): CreateRecordingSuccessResponse|ApiErrorResponse
    {
        $data = self::decodeJson($response);
        if (!self::isSuccessStatus($response)) {
            return self::error($response, $data);
        }
        return new CreateRecordingSuccessResponse(
            id: self::requiredString($data, 'id', 'recording creation'),
            statusUrl: self::requiredString($data, 'status_url', 'recording creation'),
            recordingUrl: self::requiredString($data, 'recording_url', 'recording creation'),
        );
    }

    public function recordingStatus(ResponseInterface $response): RecordingStatusSuccessResponse|ApiErrorResponse
    {
        $data = self::decodeJson($response);
        if (!self::isSuccessStatus($response)) {
            return self::error($response, $data);
        }
        $error = null;
        $rawError = $data['error'] ?? null;
        if ($rawError !== null) {
            if (!is_array($rawError)) {
                throw new UnexpectedValueException('Recording error must be an object.');
            }
            $error = new RecordingError(
                code: self::responseString($rawError['code'] ?? null),
                message: self::responseString($rawError['message'] ?? null),
                occurredAt: self::responseString($rawError['occurred_at'] ?? null),
                retryable: self::recordingBool($rawError['retryable'] ?? null) ?? false,
                actionPath: isset($rawError['action_path']) ? self::responseString($rawError['action_path']) : null,
            );
        }
        $status = RecordingStatus::tryFrom(self::requiredString($data, 'status', 'recording status')) ?? RecordingStatus::Unknown;
        return new RecordingStatusSuccessResponse(
            id: self::requiredString($data, 'id', 'recording status'),
            recordingUrl: self::requiredString($data, 'recording_url', 'recording status'),
            status: $status,
            statusEnteredAt: self::requiredString($data, 'status_entered_at', 'recording status'),
            attemptCount: self::recordingInt($data['attempt_count'] ?? null) ?? 0,
            storedDurationMs: self::recordingInt($data['stored_duration_ms'] ?? null),
            storageDestinationHctiStorageDisabled: self::recordingBool($data['storage_destination_hcti_storage_disabled'] ?? null),
            error: $error,
        );
    }

    public function deleteRecording(ResponseInterface $response): DeleteRecordingSuccessResponse|ApiErrorResponse
    {
        if (self::isSuccessStatus($response)) {
            return new DeleteRecordingSuccessResponse();
        }
        return self::error($response, self::decodeJson($response));
    }

    private static function recordingInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?[0-9]+$/D', $value) === 1) {
            return (int) $value;
        }
        throw new UnexpectedValueException('Recording response must contain integer counts and durations.');
    }

    private static function recordingBool(mixed $value): ?bool
    {
        if ($value === null || is_bool($value)) {
            return $value;
        }
        throw new UnexpectedValueException('Recording response must contain boolean flags.');
    }

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
