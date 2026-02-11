<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Document;

enum Wrap: string
{
    case NO_WRAP = 'noWrap';
    case WRAP = 'wrap';
    case WRAP_REVERSE = 'wrapReverse';
}
