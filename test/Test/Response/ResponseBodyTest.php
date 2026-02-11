<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Response;

use ArrayObject;
use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Response\Directives\Display\RenderTemplateDirective;
use Rboschin\AmazonAlexa\Response\OutputSpeech;
use Rboschin\AmazonAlexa\Response\Reprompt;
use Rboschin\AmazonAlexa\Response\ResponseBody;
use PHPUnit\Framework\TestCase;

class ResponseBodyTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $rb = new ResponseBody();
        $this->assertEquals(new ArrayObject(), $rb->jsonSerialize());
        $rb->shouldEndSession = true;
        $this->assertEquals(new ArrayObject(['shouldEndSession' => true]), $rb->jsonSerialize());
        $card = new Card();
        $rb->card = $card;
        $os = new OutputSpeech();
        $rb->outputSpeech = $os;
        $directive = new RenderTemplateDirective();
        $rb->addDirective($directive);
        $reprompt = new Reprompt($rb->outputSpeech);
        $rb->reprompt = $reprompt;
        $this->assertEquals(new ArrayObject([
            'outputSpeech' => $os,
            'card' => $card,
            'reprompt' => $reprompt,
            'shouldEndSession' => true,
            'directives' => [$directive],
        ]), $rb->jsonSerialize());
    }
}
