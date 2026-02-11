<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Display;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

class HintDirective extends Directive
{
    public const TYPE = 'Hint';

    public function __construct(
        public ?Text $hint = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(Text $text): self
    {
        return new self($text);
    }
}
