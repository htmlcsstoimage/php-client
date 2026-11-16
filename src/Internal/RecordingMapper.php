<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use BackedEnum;
use HtmlCssToImage\Request\CreateHtmlCssRecordingRequest;
use HtmlCssToImage\Request\CreateRecordingRequest;
use HtmlCssToImage\Request\CreateUrlRecordingRequest;

/** @internal */
final class RecordingMapper
{
    /** @return array<string, mixed> */
    public static function map(CreateRecordingRequest $request): array
    {
        $result = self::object($request);
        if ($request instanceof CreateHtmlCssRecordingRequest) {
            unset($result['google_fonts']);
            $fonts = RequestMapper::googleFonts($request->googleFonts);
            if ($fonts !== null) {
                $result['google_fonts'] = $fonts;
            }
        }
        if ($request instanceof CreateUrlRecordingRequest && $request->headers !== null) {
            $result['headers'] = (object) $request->headers;
        }
        return $result;
    }

    /** @return array<string, mixed> */
    private static function object(object $value): array
    {
        $result = [];
        foreach (get_object_vars($value) as $name => $item) {
            if ($item !== null) {
                $key = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name) ?? $name);
                $result[$key] = self::value($item);
            }
        }
        return $result;
    }

    private static function value(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if (is_object($value)) {
            return (object) self::object($value);
        }
        if (is_array($value)) {
            return array_map(self::value(...), $value);
        }
        return $value;
    }
}
