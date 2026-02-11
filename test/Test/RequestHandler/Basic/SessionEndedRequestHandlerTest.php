<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\RequestHandler\Basic;

use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\RequestHandler\Basic\SessionEndedRequestHandler;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Response\ResponseBody;
use PHPUnit\Framework\TestCase;

class SessionEndedRequestHandlerTest extends TestCase
{
    public function testSupportsRequestAndOutput(): void
    {
        $responseHelper = $this->getMockBuilder(ResponseHelper::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $request = Request::fromAmazonRequest('{"request":{"type":"SessionEndedRequest", "requestId":"requestId", "timestamp":"' . time() . '", "locale":"en-US"}}', 'true', 'true');
        $output = 'Just a simple Test';
        $requestHandler = new SessionEndedRequestHandler($responseHelper, $output, ['my_skill_id']);

        $responseBody = new ResponseBody();
        $responseBody->outputSpeech = $output;
        $responseHelper->expects(static::once())->method('respond')->willReturn(new Response([], '1.0', $responseBody));

        static::assertTrue($requestHandler->supportsRequest($request));
        static::assertSame($output, $requestHandler->handleRequest($request)->response->outputSpeech);
    }

    public function testNotSupportsRequest(): void
    {
        $request = Request::fromAmazonRequest('{"request":{"type":"IntentRequest", "requestId":"requestId", "timestamp":"' . time() . '", "locale":"en-US", "intent":{"name":"InvalidIntent"}}}', 'true', 'true');
        $output = 'Just a simple Test';
        $requestHandler = new SessionEndedRequestHandler(new ResponseHelper(), $output, ['my_skill_id']);

        static::assertFalse($requestHandler->supportsRequest($request));
    }
}
