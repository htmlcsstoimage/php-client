<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Look for a matching element and execute the corresponding branch. Branch actions supply their own selectors.
 * @see https://docs.htmlcsstoimage.com/parameters/actions/
 * @api
 */
final readonly class RecordingLocateSelectorAction extends RecordingAction
{
    /**
     * @param string $selector CSS selector to find.
     * @param int|null $timeoutMs Maximum wait in milliseconds. Defaults to 0 for a single check.
     * @param list<RecordingAction>|null $ifFound Actions when found. Omitted, null, or empty continues
     *     with the parent sequence.
     * @param list<RecordingAction>|null $ifNotFound Actions when missing. Include an error action to
     *     fail the recording.
     */
    public function __construct(
        public string $selector,
        public ?int $timeoutMs = null,
        public ?array $ifFound = null,
        public ?array $ifNotFound = null,
    ) {
        parent::__construct('locate_selector');
    }
}
