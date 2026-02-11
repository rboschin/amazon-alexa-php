<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Component;

use Rboschin\AmazonAlexa\Response\Directives\APL\Component\Traits\MultiChildComponentTrait;
use Rboschin\AmazonAlexa\Response\Directives\APL\Document\APLComponentType;

abstract class MultiChildComponent extends APLBaseComponent
{
    use MultiChildComponentTrait;

    public function __construct(APLComponentType $type)
    {
        parent::__construct($type);
    }
}
