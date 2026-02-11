<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response;

class Reprompt
{
    public function __construct(public OutputSpeech $outputSpeech)
    {
    }
}
