<?php

declare(strict_types=1);

namespace HtmlCssToImage\Response;

/**
 * Successful batch image creation response.
 *
 * @api
 */
final readonly class CreateImageBatchSuccessResponse
{
    /** Always true for this response type. */
    public true $success;

    /**
     * @param list<CreateImageSuccessResponse> $images Batch image results.
     */
    public function __construct(
        public array $images,
    ) {
        $this->success = true;
    }
}
