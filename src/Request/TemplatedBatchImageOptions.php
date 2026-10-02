<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

use HtmlCssToImage\ImageFormat;

/** Shared defaults or one template batch variation. @api */
final readonly class TemplatedBatchImageOptions
{
    /**
     * @param string|null $templateId Omit to inherit; supplying an ID resets the inherited version.
     * @param array<string, mixed>|null $templateValues Objects merge recursively; arrays, scalars and null replace defaults.
     * @param int|null $templateVersion Inherit the version, or use latest when supplying a template ID.
     * @param ImageFormat|null $format File format of the returned URL.
     */
    public function __construct(
        public ?string $templateId = null,
        public ?array $templateValues = null,
        public ?int $templateVersion = null,
        public ?ImageFormat $format = null,
    ) {
    }
}
