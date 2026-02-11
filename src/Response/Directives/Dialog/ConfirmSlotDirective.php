<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Dialog;

use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Response\Directives\Directive;

class ConfirmSlotDirective extends Directive
{
    public const TYPE = 'Dialog.ConfirmSlot';

    public function __construct(
        public ?string $slotToConfirm = null,
        public ?Intent $updatedIntent = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(string $slotToConfirm, ?Intent $intent = null): self
    {
        return new self($slotToConfirm, $intent);
    }
}
