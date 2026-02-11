<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Dialog;

use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Response\Directives\Directive;

class ElicitSlotDirective extends Directive
{
    public const TYPE = 'Dialog.ElicitSlot';

    public function __construct(
        public ?string $slotToElicit = null,
        public ?Intent $updatedIntent = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(string $slotToElicit, ?Intent $intent = null): self
    {
        return new self($slotToElicit, $intent);
    }
}
