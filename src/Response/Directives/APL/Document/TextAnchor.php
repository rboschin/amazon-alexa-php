<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum TextAnchor: string
{
    case START = 'start';
    case MIDDLE = 'middle';
    case END = 'end';
}
