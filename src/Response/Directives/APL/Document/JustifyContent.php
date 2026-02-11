<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum JustifyContent: string
{
    case START = 'start';
    case END = 'end';
    case CENTER = 'center';
    case SPACE_BETWEEN = 'spaceBetween';
    case SPACE_AROUND = 'spaceAround';
}
