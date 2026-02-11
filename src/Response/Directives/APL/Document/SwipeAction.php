<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum SwipeAction: string
{
    case REVEAL = 'reveal';
    case SLIDE = 'slide';
    case COVER = 'cover';
}
