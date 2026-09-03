<?php

declare(strict_types=1);

namespace HtmlCssToImage\Internal;

use Composer\InstalledVersions;

/**
 * Provides package metadata used in outbound API requests.
 *
 * @internal
 */
final class LibraryInfo
{
    private const PACKAGE_NAME = 'html-css-to-image/client';

    /** Return the SDK product token and its Composer-installed version. */
    public static function userAgent(): string
    {
        /** @var string|null $userAgent */
        static $userAgent = null;
        if ($userAgent !== null) {
            return $userAgent;
        }

        $version = InstalledVersions::isInstalled(self::PACKAGE_NAME)
            ? InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
            : null;
        $version = preg_replace('/^v(?=\d)/i', '', $version ?? 'unknown')
            ?? 'unknown';
        $version = preg_replace(
            '/[^A-Za-z0-9._+-]/',
            '-',
            $version,
        ) ?? 'unknown';

        return $userAgent = "HCTIPHP/{$version}";
    }
}
