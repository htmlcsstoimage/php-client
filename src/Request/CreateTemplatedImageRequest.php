<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\ImageFormat;

/**
 * Request to render a saved HCTI template.
 *
 * @api
 */
final readonly class CreateTemplatedImageRequest implements CreateImageRequest
{
    /**
     * @param string $templateId Identifier of the saved template.
     * @param array<string, mixed> $templateValues Template variable values.
     * @param int|null $templateVersion Specific version, or latest when null.
     * @param ImageFormat|null $format File format used in the returned URL.
     */
    public function __construct(
        public string $templateId,
        public array $templateValues,
        public ?int $templateVersion = null,
        public ?ImageFormat $format = null,
    ) {
    }
}
