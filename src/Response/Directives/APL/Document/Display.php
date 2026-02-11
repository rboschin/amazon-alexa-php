<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum Display: string
{
    case INVISIBLE = 'invisible';
    case NONE = 'none';
    case NORMAL = 'normal';
}
