<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Component;

use Rboschin\AmazonAlexa\Response\Directives\APL\Component\Traits\ActionableComponentTrait;
use Rboschin\AmazonAlexa\Response\Directives\APL\Document\APLComponentType;

abstract class ActionableComponent extends APLBaseComponent implements \JsonSerializable
{
    use ActionableComponentTrait;

    public function __construct(APLComponentType $type, ?array $preserve = null)
    {
        parent::__construct(type: $type, preserve: $preserve);
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            $this->serializeActionableProperties()
        );
    }
}
