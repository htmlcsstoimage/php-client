<?php

declare(strict_types=1);

namespace HtmlCssToImage\Request;

/** Block browser requests matching a URL wildcard and/or resource types. */
final readonly class RequestOverride
{
    /** @param list<RequestOverrideResourceType>|null $resourceTypes */
    public function __construct(
        public ?string $url = null,
        public ?array $resourceTypes = null,
        public RequestOverrideAction $action = RequestOverrideAction::Block,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = ['action' => $this->action->value];
        if ($this->url !== null) {
            $result['url'] = $this->url;
        }
        if ($this->resourceTypes !== null) {
            $result['resource_types'] = array_map(
                static fn (RequestOverrideResourceType $type): string => $type->value,
                $this->resourceTypes,
            );
        }

        return $result;
    }
}
