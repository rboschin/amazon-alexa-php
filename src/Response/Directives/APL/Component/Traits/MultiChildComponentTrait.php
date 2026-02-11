<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Component\Traits;

use Rboschin\AmazonAlexa\Response\Directives\APL\Component\APLBaseComponent;

trait MultiChildComponentTrait
{
    public ?array $data = null;
    /** @var APLBaseComponent|APLBaseComponent[]|null */
    public $item = null;
    /** @var APLBaseComponent[]|null */
    public ?array $items = null;

    protected function serializeMultiChildProperties(): array
    {
        $data = [];

        if ($this->data !== null && !empty($this->data)) {
            $data['data'] = $this->data;
        }

        if ($this->item !== null) {
            $data['item'] = $this->item;
        }

        if ($this->items !== null && !empty($this->items)) {
            $data['items'] = $this->items;
        }

        return $data;
    }
}
