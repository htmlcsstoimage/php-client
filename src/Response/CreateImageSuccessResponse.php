<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/**
 * Successful image creation response.
 *
 * @api
 */
final readonly class CreateImageSuccessResponse
{
    /** Always true for this response type. */
    public true $success;

    /**
     * @param string $id Identifier assigned to the generated image.
     * @param string $url URL from which the image can be retrieved.
     */
    public function __construct(
        public string $id,
        public string $url,
    ) {
        $this->success = true;
    }
}
