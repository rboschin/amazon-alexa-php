<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Dialog\UpdateDynamicEntities;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

abstract class UpdateDynamicEntities extends Directive
{
    public const TYPE = 'Dialog.UpdateDynamicEntities';

    public function __construct(
        public string $updateBehavior = ''
    ) {
        parent::__construct(self::TYPE);
    }
}
