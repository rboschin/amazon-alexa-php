<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Dialog;

use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Response\Directives\Directive;

class ConfirmIntentDirective extends Directive
{
    public const TYPE = 'Dialog.ConfirmIntent';

    public function __construct(
        public ?Intent $updatedIntent = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(?Intent $intent = null): self
    {
        return new self($intent);
    }
}
