<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Execute JavaScript in the page while recording continues, awaiting any returned promise.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingExecuteJavaScriptAction extends RecordingAction
{
    /**
     * @param string $script Function body supporting await. Its return value is ignored.
     * @param int|null $timeoutMs Maximum execution time in milliseconds. Zero or omitted uses the page
     *     timeout; maximum 90000.
     * @param list<RecordingAction>|null $ifError Omitted or null fails on script errors; [] ignores
     *     errors. A timeout does not undo side effects.
     */
    public function __construct(
        public string $script,
        public ?int $timeoutMs = null,
        public ?array $ifError = null,
    ) {
        parent::__construct('execute_javascript');
    }
}
