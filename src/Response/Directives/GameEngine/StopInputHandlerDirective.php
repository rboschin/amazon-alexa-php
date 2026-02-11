<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\GameEngine;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

class StopInputHandlerDirective extends Directive
{
    public const TYPE = 'GameEngine.StopInputHandler';

    public function __construct(
        public ?string $originatingRequestId = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(string $originatingRequestId): self
    {
        return new self($originatingRequestId);
    }
}
