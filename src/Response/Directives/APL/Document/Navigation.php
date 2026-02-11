<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum Navigation: string
{
    case NORMAL = 'normal';
    case NONE = 'none';
    case WRAP = 'wrap';
    case FORWARD_ONLY = 'forward-only';
}
