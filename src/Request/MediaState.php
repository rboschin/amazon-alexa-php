<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Request;

enum MediaState: string
{
    case IDLE = 'idle';
    case PLAYING = 'playing';
    case PAUSED = 'paused';
}
