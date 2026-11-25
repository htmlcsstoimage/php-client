<?php

declare(strict_types=1);

namespace HtmlCssToImage\Tests;

use GuzzleHttp\Psr7\Response;
use HtmlCssToImage\HtmlCssToImageClient;
use HtmlCssToImage\HtmlCssToImageClientInterface;
use HtmlCssToImage\Recording\CoordinatesScrollTarget;
use HtmlCssToImage\Recording\PositionScrollTarget;
use HtmlCssToImage\Recording\RecordingAssetAudio;
use HtmlCssToImage\Recording\RecordingAudioEndBehavior;
use HtmlCssToImage\Recording\RecordingUrlAudio;
use HtmlCssToImage\Recording\RecordingClickAction;
use HtmlCssToImage\Recording\RecordingDurationMode;
use HtmlCssToImage\Recording\RecordingElementVisibleTrigger;
use HtmlCssToImage\Recording\RecordingErrorAction;
use HtmlCssToImage\Recording\RecordingExecuteJavaScriptAction;
use HtmlCssToImage\Recording\RecordingFormat;
use HtmlCssToImage\Recording\RecordingFunctionCallTrigger;
use HtmlCssToImage\Recording\RecordingImmediateTrigger;
use HtmlCssToImage\Recording\RecordingLocateSelectorAction;
use HtmlCssToImage\Recording\RecordingOutputOptions;
use HtmlCssToImage\Recording\RecordingScrollAction;
use HtmlCssToImage\Recording\RecordingSimpleScrollAction;
use HtmlCssToImage\Recording\RecordingStallAction;
use HtmlCssToImage\Recording\RecordingStatus;
use HtmlCssToImage\Recording\ScrollPosition;
use HtmlCssToImage\Recording\ScrollStep;
use HtmlCssToImage\Recording\SelectorScrollTarget;
use HtmlCssToImage\Request\CreateHtmlCssRecordingRequest;
use HtmlCssToImage\Request\CreateUrlRecordingRequest;
use HtmlCssToImage\Request\RequestOverride;
use HtmlCssToImage\Request\RequestOverrideResourceType;
use HtmlCssToImage\Response\ApiErrorResponse;
use HtmlCssToImage\Response\CreateRecordingSuccessResponse;
use HtmlCssToImage\Response\DeleteRecordingSuccessResponse;
use HtmlCssToImage\Response\RecordingStatusSuccessResponse;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

final class RecordingsTest extends TestCase
{
    private const CREATED = '{"id":"recording-id","status_url":"https://hcti.io/v1/recordings/recording-id/status","recording_url":"https://hcti.io/v1/recording/recording-id.mp4"}';

    public function testHtmlCreationMapsFontsAndUsesExistingAuthentication(): void
    {
        $http = new QueueHttpClient([new Response(200, [], self::CREATED)]);
        $client = new HtmlCssToImageClient('id', 'key', $http);
        $request = new CreateHtmlCssRecordingRequest(
            html: '<h1>Hello</h1>', durationMs: 1000,
            googleFonts: [' Roboto ', 'Open Sans', 'Open Sans'],
            audio: new RecordingAssetAudio(assetId: 'asset_1'),
        );
        $result = $client->createRecording($request);
        self::assertInstanceOf(CreateRecordingSuccessResponse::class, $result);
        self::assertTrue($result->success);
        self::assertSame('recording-id', $result->id);
        self::assertSame('https://hcti.io/v1/recordings/recording-id/status', $result->statusUrl);
        self::assertSame('https://hcti.io/v1/recording/recording-id.mp4', $result->recordingUrl);
        $sent = $http->requests[0];
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('/v1/recording', $sent->getUri()->getPath());
        self::assertSame('Basic aWQ6a2V5', $sent->getHeaderLine('Authorization'));
        self::assertSame('application/json', $sent->getHeaderLine('Content-Type'));
        self::assertSame([
            'duration_ms' => 1000, 'audio' => ['asset_id' => 'asset_1'], 'html' => '<h1>Hello</h1>',
            'google_fonts' => 'Roboto|Open+Sans',
        ], json_decode((string) $sent->getBody(), true, flags: JSON_THROW_ON_ERROR));
        self::assertSame([' Roboto ', 'Open Sans', 'Open Sans'], $request->googleFonts);
    }

    public function testUrlOptionsAndNestedTimelinePreserveEmptyBranches(): void
    {
        $http = new QueueHttpClient([new Response(200, [], self::CREATED)]);
        $client = new HtmlCssToImageClient('id', 'key', $http);
        $request = new CreateUrlRecordingRequest(
            url: 'https://example.com', durationMs: 5000,
            css: 'body { color: red; }', deviceScale: 2,
            viewportWidth: 1280, viewportHeight: 720,
            dedupeDurationS: 0, disableTwemoji: false, colorScheme: 'dark',
            timezone: 'America/New_York', viewportMobile: false,
            viewportLandscape: true, viewportTouch: false, mediaType: 'screen',
            proxyId: 'proxy', storageDestinationId: 'store',
            headers: ['Authorization' => 'Bearer test'],
            additionalHeaderOrigins: ['https://assets.example.com'],
            includeHeadersOnSubrequests: false, identifyAsHcti: true,
            blockConsentBanners: true, deferRender: false,
            skipScrollPreload: true, durationMode: RecordingDurationMode::AtLeast,
            requestOverrides: [new RequestOverride(resourceTypes: [RequestOverrideResourceType::Script])],
            output: new RecordingOutputOptions(format: RecordingFormat::WebM, fps: 60, scale: 0.5),
            audio: new RecordingUrlAudio(
                url: 'https://cdn.example.com/audio/intro.mp3', sourceStartMs: 0, delayMs: 500,
                whenAudioEnds: RecordingAudioEndBehavior::TrimVideo, errorOnSourceInvalid: false,
            ),
            trigger: new RecordingElementVisibleTrigger(selector: '#ready'),
            actions: [
                new RecordingLocateSelectorAction(selector: '#modal', ifNotFound: [], ifFound: [
                    new RecordingClickAction(selector: '#close', ifNotFound: [new RecordingErrorAction()]),
                ]),
                new RecordingExecuteJavaScriptAction(script: 'await Promise.resolve();'),
                new RecordingExecuteJavaScriptAction(script: 'throw Error();', ifError: []),
                new RecordingScrollAction(steps: [
                    new ScrollStep(to: new PositionScrollTarget(position: ScrollPosition::Bottom), durationMs: 500),
                    new ScrollStep(to: new CoordinatesScrollTarget(x: 0, y: 200), durationMs: 250),
                    new ScrollStep(to: new SelectorScrollTarget(selector: '#footer', offsetY: -40, ifNotFound: []), durationMs: 800),
                ], ifNotFound: [new RecordingStallAction(durationMs: 0)]),
                new RecordingSimpleScrollAction(scrollDistance: '90vh'),
                new RecordingSimpleScrollAction(scrollDistance: 900),
            ],
        );
        self::assertTrue($client->createRecording($request)->success);
        $data = json_decode((string) $http->requests[0]->getBody(), true, flags: JSON_THROW_ON_ERROR);
        self::assertEquals([
            'url' => 'https://cdn.example.com/audio/intro.mp3', 'source_start_ms' => 0, 'delay_ms' => 500,
            'when_audio_ends' => 'trim_video', 'error_on_source_invalid' => false,
        ], $data['audio']);
        foreach ([
            'viewport_width' => 1280, 'viewport_height' => 720, 'device_scale' => 2,
            'dedupe_duration_s' => 0, 'disable_twemoji' => false, 'color_scheme' => 'dark',
            'timezone' => 'America/New_York', 'viewport_mobile' => false,
            'viewport_landscape' => true, 'viewport_touch' => false, 'media_type' => 'screen',
            'proxy_id' => 'proxy', 'storage_destination_id' => 'store',
            'include_headers_on_subrequests' => false, 'identify_as_hcti' => true,
            'block_consent_banners' => true, 'defer_render' => false,
            'skip_scroll_preload' => true, 'duration_mode' => 'at_least',
        ] as $key => $expected) {
            self::assertSame($expected, $data[$key], $key);
        }
        self::assertSame(['format' => 'webm', 'fps' => 60, 'scale' => 0.5], $data['output']);
        self::assertSame('element_visible', $data['trigger']['type']);
        self::assertEquals([['action' => 'block', 'resource_types' => ['script']]], $data['request_overrides']);
        $actions = $data['actions'];
        self::assertSame([], $actions[0]['if_not_found']);
        self::assertSame([['type' => 'error']], $actions[0]['if_found'][0]['if_not_found']);
        self::assertArrayNotHasKey('if_error', $actions[1]);
        self::assertSame([], $actions[2]['if_error']);
        self::assertSame(['position', 'coordinates', 'selector'], array_column(array_column($actions[3]['steps'], 'to'), 'type'));
        self::assertSame([500, 250, 800], array_column($actions[3]['steps'], 'duration_ms'));
        self::assertSame('90vh', $actions[4]['scroll_distance']);
        self::assertSame(900, $actions[5]['scroll_distance']);
    }

    public function testDefaultsAndEmptyOutputObjectsAreNotTurnedIntoArrays(): void
    {
        foreach ([new RecordingImmediateTrigger(), new RecordingFunctionCallTrigger(timeoutMs: 1000)] as $trigger) {
            $http = new QueueHttpClient([new Response(200, [], self::CREATED)]);
            $client = new HtmlCssToImageClient('id', 'key', $http);
            $client->createRecording(new CreateUrlRecordingRequest(
                url: '', durationMs: 0, trigger: $trigger,
                output: new RecordingOutputOptions(), headers: [],
            ));
            $data = json_decode((string) $http->requests[0]->getBody(), flags: JSON_THROW_ON_ERROR);
            self::assertSame(0, $data->duration_ms);
            self::assertSame('', $data->url);
            self::assertSame($trigger->type, $data->trigger->type);
            self::assertEquals((object) [], $data->output);
            self::assertEquals((object) [], $data->headers);
            self::assertFalse(property_exists($data, 'duration_mode'));
        }
    }

    public function testAllStatusesAndAttemptInformationUseTypedResponses(): void
    {
        foreach (RecordingStatus::cases() as $status) {
            $httpStatus = in_array($status->value, ['ready', 'failed', 'cancelled'], true) ? 200 : 202;
            $body = [
                'id' => 'recording-id', 'recording_url' => 'https://hcti.io/rec.mp4',
                'status' => $status->value, 'status_entered_at' => '2026-11-16T10:00:00Z',
                'attempt_count' => 2, 'stored_duration_ms' => null,
                'storage_destination_hcti_storage_disabled' => true,
                'error' => [
                    'code' => 'action_error', 'message' => 'Missing element',
                    'occurred_at' => '2026-11-16T09:59:00Z', 'retryable' => true,
                    'action_path' => 'actions[1]',
                ],
            ];
            $http = new QueueHttpClient([new Response($httpStatus, [], json_encode($body, JSON_THROW_ON_ERROR))]);
            $result = (new HtmlCssToImageClient('id', 'key', $http))->fetchRecordingStatus('recording-id');
            self::assertInstanceOf(RecordingStatusSuccessResponse::class, $result);
            self::assertTrue($result->success);
            self::assertSame($status, $result->status);
            self::assertSame(2, $result->attemptCount);
            self::assertNull($result->storedDurationMs);
            self::assertTrue($result->storageDestinationHctiStorageDisabled);
            self::assertSame('actions[1]', $result->error->actionPath);
            self::assertTrue($result->error->retryable);
            self::assertSame('GET', $http->requests[0]->getMethod());
            self::assertSame('/v1/recordings/recording-id/status', $http->requests[0]->getUri()->getPath());
        }
    }

    public function testDeleteMethodsAcceptEmptyBodies(): void
    {
        $http = new QueueHttpClient([new Response(202), new Response(202)]);
        $client = new HtmlCssToImageClient('id', 'key', $http);
        self::assertInstanceOf(DeleteRecordingSuccessResponse::class, $client->deleteRecording('recording-id'));
        self::assertTrue($client->deleteRecordingBatch(['one', 'two'])->success);
        self::assertSame('DELETE', $http->requests[0]->getMethod());
        self::assertSame('/v1/recordings/recording-id', $http->requests[0]->getUri()->getPath());
        self::assertSame('', (string) $http->requests[0]->getBody());
        self::assertSame('{"ids":["one","two"]}', (string) $http->requests[1]->getBody());
    }

    public function testEmptyBatchDeleteSucceedsWithoutRequest(): void
    {
        $http = new QueueHttpClient([]);
        $result = (new HtmlCssToImageClient('id', 'key', $http))->deleteRecordingBatch([]);
        self::assertInstanceOf(DeleteRecordingSuccessResponse::class, $result);
        self::assertTrue($result->success);
        self::assertSame([], $http->requests);
    }

    public function testUnrecognizedStatusMapsToUnknown(): void
    {
        $body = '{"id":"recording-id","recording_url":"https://hcti.io/rec.mp4","status":"archiving","status_entered_at":"2026-11-16T10:00:00Z"}';
        $http = new QueueHttpClient([new Response(202, [], $body)]);
        $result = (new HtmlCssToImageClient('id', 'key', $http))->fetchRecordingStatus('recording-id');
        self::assertInstanceOf(RecordingStatusSuccessResponse::class, $result);
        self::assertSame(RecordingStatus::Unknown, $result->status);
    }

    public function testMethodsShareApiErrorsAndTransportFailures(): void
    {
        foreach (['create', 'status', 'delete', 'batch'] as $operation) {
            foreach ([new Response(403, [], '{"error":"Forbidden","message":"Missing permission"}'), new Response(502, [], 'Unavailable')] as $response) {
                $http = new QueueHttpClient([$response]);
                $result = $this->call(new HtmlCssToImageClient('id', 'key', $http), $operation);
                self::assertInstanceOf(ApiErrorResponse::class, $result);
                self::assertFalse($result->success);
                self::assertSame($response->getStatusCode(), $result->statusCode);
            }
            try {
                $this->call(new HtmlCssToImageClient('id', 'key', new QueueHttpClient([])), $operation);
                self::fail('Expected transport failure');
            } catch (RuntimeException $error) {
                self::assertSame('No queued response', $error->getMessage());
            }
        }
    }

    public function testMalformedCreationUsesExistingResponseException(): void
    {
        $http = new QueueHttpClient([new Response(200, [], '{}')]);
        $this->expectException(UnexpectedValueException::class);
        (new HtmlCssToImageClient('id', 'key', $http))->createRecording(
            new CreateUrlRecordingRequest(url: 'https://example.com', durationMs: 1000),
        );
    }

    private function call(HtmlCssToImageClientInterface $client, string $operation): object
    {
        return match ($operation) {
            'create' => $client->createRecording(new CreateUrlRecordingRequest(url: 'https://example.com', durationMs: 1000)),
            'status' => $client->fetchRecordingStatus('recording-id'),
            'delete' => $client->deleteRecording('recording-id'),
            default => $client->deleteRecordingBatch(['recording-id']),
        };
    }
}
