<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/**
 * Successful image deletion response.
 *
 * @api
 */
final readonly class DeleteImageSuccessResponse
{
    /** Always true for this response type. */
    public true $success;

    /** Mark any successful 2xx deletion response as successful. */
    public function __construct()
    {
        $this->success = true;
    }
}
