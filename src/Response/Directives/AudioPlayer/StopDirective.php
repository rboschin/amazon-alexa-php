<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\AudioPlayer;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

class StopDirective extends Directive
{
    public const TYPE = 'AudioPlayer.Stop';

    public function __construct()
    {
        parent::__construct(self::TYPE);
    }

    public static function create(): self
    {
        return new self();
    }
}
