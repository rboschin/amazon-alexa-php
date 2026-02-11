<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\RequestHandler;

use Rboschin\AmazonAlexa\Exception\MissingRequestHandlerException;
use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\SessionEndedRequest;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\Response\Response;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for IntentRouter
 */
class IntentRouterTest extends TestCase
{
    private IntentRouter $router;
    private TestHandler $testHandler1;
    private TestHandler $testHandler2;
    private TestHandler $launchHandler;
    private TestHandler $sessionEndedHandler;
    private TestHandler $fallbackHandler;

    protected function setUp(): void
    {
        $this->router = new IntentRouter();
        $this->testHandler1 = new TestHandler('Handler1');
        $this->testHandler2 = new TestHandler('Handler2');
        $this->launchHandler = new TestHandler('LaunchHandler');
        $this->sessionEndedHandler = new TestHandler('SessionEndedHandler');
        $this->fallbackHandler = new TestHandler('FallbackHandler');
    }

    public function testOnIntentReturnsSelf(): void
    {
        $result = $this->router->onIntent('TestIntent', $this->testHandler1);
        
        $this->assertInstanceOf(IntentRouter::class, $result);
        $this->assertSame($this->router, $result);
    }

    public function testOnLaunchReturnsSelf(): void
    {
        $result = $this->router->onLaunch($this->launchHandler);
        
        $this->assertInstanceOf(IntentRouter::class, $result);
        $this->assertSame($this->router, $result);
    }

    public function testOnSessionEndedReturnsSelf(): void
    {
        $result = $this->router->onSessionEnded($this->sessionEndedHandler);
        
        $this->assertInstanceOf(IntentRouter::class, $result);
        $this->assertSame($this->router, $result);
    }

    public function testOnFallbackReturnsSelf(): void
    {
        $result = $this->router->onFallback($this->fallbackHandler);
        
        $this->assertInstanceOf(IntentRouter::class, $result);
        $this->assertSame($this->router, $result);
    }

    public function testGetHandlerForIntentRequest(): void
    {
        $this->router->onIntent('TestIntent', $this->testHandler1);
        
        $request = $this->createIntentRequest('TestIntent');
        $handler = $this->router->getHandlerFor($request);
        
        $this->assertSame($this->testHandler1, $handler);
    }

    public function testGetHandlerForLaunchRequest(): void
    {
        $this->router->onLaunch($this->launchHandler);
        
        $request = $this->createLaunchRequest();
        $handler = $this->router->getHandlerFor($request);
        
        $this->assertSame($this->launchHandler, $handler);
    }

    public function testGetHandlerForSessionEndedRequest(): void
    {
        $this->router->onSessionEnded($this->sessionEndedHandler);
        
        $request = $this->createSessionEndedRequest();
        $handler = $this->router->getHandlerFor($request);
        
        $this->assertSame($this->sessionEndedHandler, $handler);
    }

    public function testGetHandlerForUnknownIntentReturnsFallback(): void
    {
        $this->router->onIntent('KnownIntent', $this->testHandler1);
        $this->router->onFallback($this->fallbackHandler);
        
        $request = $this->createIntentRequest('UnknownIntent');
        $handler = $this->router->getHandlerFor($request);
        
        $this->assertSame($this->fallbackHandler, $handler);
    }

    public function testGetHandlerForUnknownRequestWithoutFallbackThrowsException(): void
    {
        $this->expectException(MissingRequestHandlerException::class);
        
        $request = $this->createIntentRequest('UnknownIntent');
        $this->router->getHandlerFor($request);
    }

    public function testHasHandlerForKnownIntent(): void
    {
        $this->router->onIntent('TestIntent', $this->testHandler1);
        
        $request = $this->createIntentRequest('TestIntent');
        
        $this->assertTrue($this->router->hasHandlerFor($request));
    }

    public function testHasHandlerForUnknownIntent(): void
    {
        $request = $this->createIntentRequest('UnknownIntent');
        
        $this->assertFalse($this->router->hasHandlerFor($request));
    }

    public function testGetRegisteredIntents(): void
    {
        $this->router->onIntent('Intent1', $this->testHandler1);
        $this->router->onIntent('Intent2', $this->testHandler2);
        
        $intents = $this->router->getRegisteredIntents();
        
        $this->assertCount(2, $intents);
        $this->assertContains('Intent1', $intents);
        $this->assertContains('Intent2', $intents);
    }

    public function testHasIntent(): void
    {
        $this->router->onIntent('TestIntent', $this->testHandler1);
        
        $this->assertTrue($this->router->hasIntent('TestIntent'));
        $this->assertFalse($this->router->hasIntent('UnknownIntent'));
    }

    public function testRemoveIntent(): void
    {
        $this->router->onIntent('TestIntent', $this->testHandler1);
        
        $this->assertTrue($this->router->hasIntent('TestIntent'));
        
        $this->router->removeIntent('TestIntent');
        
        $this->assertFalse($this->router->hasIntent('TestIntent'));
    }

    public function testClear(): void
    {
        $this->router->onIntent('TestIntent', $this->testHandler1);
        $this->router->onLaunch($this->launchHandler);
        $this->router->onFallback($this->fallbackHandler);
        
        $result = $this->router->clear();
        
        $this->assertInstanceOf(IntentRouter::class, $result);
        $this->assertSame($this->router, $result);
        $this->assertEmpty($this->router->getRegisteredIntents());
    }

    private function createIntentRequest(string $intentName): Request
    {
        $intent = new Intent();
        $intent->name = $intentName;
        
        $intentRequest = new IntentRequest(
            requestId: 'test-request-id',
            timestamp: new \DateTime(),
            locale: 'en-US',
            intent: $intent
        );

        return new Request(
            version: '1.0',
            request: $intentRequest,
            amazonRequestBody: '{}',
            signatureCertChainUrl: '',
            signature: ''
        );
    }

    private function createLaunchRequest(): Request
    {
        $launchRequest = new LaunchRequest(
            requestId: 'test-launch-id',
            timestamp: new \DateTime(),
            locale: 'en-US'
        );

        return new Request(
            version: '1.0',
            request: $launchRequest,
            amazonRequestBody: '{}',
            signatureCertChainUrl: '',
            signature: ''
        );
    }

    private function createSessionEndedRequest(): Request
    {
        $sessionEndedRequest = new SessionEndedRequest(
            requestId: 'test-session-ended-id',
            timestamp: new \DateTime(),
            locale: 'en-US',
            reason: 'USER_INITIATED'
        );

        return new Request(
            version: '1.0',
            request: $sessionEndedRequest,
            amazonRequestBody: '{}',
            signatureCertChainUrl: '',
            signature: ''
        );
    }
}

/**
 * Test handler for testing purposes
 */
class TestHandler extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler
{
    public function __construct(private string $name)
    {
    }

    public function supportsApplication(Request $request): bool
    {
        return true;
    }

    public function supportsRequest(Request $request): bool
    {
        return true;
    }

    public function handleRequest(Request $request): Response
    {
        $response = new Response();
        $response->response->outputSpeech = \Rboschin\AmazonAlexa\Response\OutputSpeech::createByText("Response from {$this->name}");
        $response->response->shouldEndSession = false;
        
        return $response;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
