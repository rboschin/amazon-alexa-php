<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum InteractionMode: string
{
    case INLINE = 'INLINE';
    case STANDARD = 'STANDARD';
}
