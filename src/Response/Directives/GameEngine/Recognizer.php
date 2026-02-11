<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\GameEngine;

abstract class Recognizer
{
    public function __construct(
        public string $type = ''
    ) {
    }
}
