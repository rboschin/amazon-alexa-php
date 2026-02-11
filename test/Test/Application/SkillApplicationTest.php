<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Application;

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Validation\RequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Test suite for SkillApplication
 */
class SkillApplicationTest extends TestCase
{
    private RequestValidator $validator;
    private RequestHandlerRegistry $registry;
    private TestRequestHandler $testHandler;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(RequestValidator::class);
        $this->registry = new RequestHandlerRegistry();
        $this->testHandler = new TestRequestHandler();
        $this->registry->addHandler($this->testHandler);
    }

    public function testFromGlobalsCreatesInstance(): void
    {
        $app = SkillApplication::fromGlobals();
        
        $this->assertInstanceOf(SkillApplication::class, $app);
    }

    public function testHandleRawWithValidRequest(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate');

        $app = new SkillApplication($this->validator, $this->registry);
        
        $requestBody = json_encode([
            'version' => '1.0',
            'request' => [
                'type' => 'LaunchRequest',
                'requestId' => 'test-request-id',
                'timestamp' => date('c'),
                'locale' => 'en-US'
            ],
            'session' => [
                'sessionId' => 'test-session-id',
                'application' => ['applicationId' => 'test-app-id'],
                'new' => true
            ]
        ]);

        $headers = [
            'HTTP_SIGNATURECERTCHAINURL' => 'https://example.com/cert',
            'HTTP_SIGNATURE' => 'test-signature'
        ];

        $response = $app->handleRaw($requestBody, $headers);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Test response', $response->response->outputSpeech->text);
    }

    public function testHandleRawWithInvalidSignatureReturnsErrorResponse(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willThrowException(new \Exception('Invalid signature'));

        $app = new SkillApplication($this->validator, $this->registry);
        
        $requestBody = json_encode([
            'version' => '1.0',
            'request' => [
                'type' => 'LaunchRequest',
                'requestId' => 'test-request-id',
                'timestamp' => date('c'),
                'locale' => 'en-US'
            ],
            'session' => [
                'sessionId' => 'test-session-id',
                'application' => ['applicationId' => 'test-app-id'],
                'new' => true
            ]
        ]);

        $headers = [
            'HTTP_SIGNATURECERTCHAINURL' => 'https://example.com/cert',
            'HTTP_SIGNATURE' => 'test-signature'
        ];

        $response = $app->handleRaw($requestBody, $headers);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('something went wrong', $response->response->outputSpeech->text);
        $this->assertTrue($response->response->shouldEndSession);
    }

    public function testHandleRawWithNoHandlerReturnsErrorResponse(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate');

        $emptyRegistry = new RequestHandlerRegistry();
        $app = new SkillApplication($this->validator, $emptyRegistry);
        
        $requestBody = json_encode([
            'version' => '1.0',
            'request' => [
                'type' => 'LaunchRequest',
                'requestId' => 'test-request-id',
                'timestamp' => date('c'),
                'locale' => 'en-US'
            ],
            'session' => [
                'sessionId' => 'test-session-id',
                'application' => ['applicationId' => 'test-app-id'],
                'new' => true
            ]
        ]);

        $headers = [
            'HTTP_SIGNATURECERTCHAINURL' => 'https://example.com/cert',
            'HTTP_SIGNATURE' => 'test-signature'
        ];

        $response = $app->handleRaw($requestBody, $headers);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('cannot handle this request', $response->response->outputSpeech->text);
        $this->assertTrue($response->response->shouldEndSession);
    }

    public function testHandlePsrRequest(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('validate');

        $app = new SkillApplication($this->validator, $this->registry);
        
        $psrRequest = $this->createMock(ServerRequestInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        
        $requestBody = json_encode([
            'version' => '1.0',
            'request' => [
                'type' => 'LaunchRequest',
                'requestId' => 'test-request-id',
                'timestamp' => date('c'),
                'locale' => 'en-US'
            ],
            'session' => [
                'sessionId' => 'test-session-id',
                'application' => ['applicationId' => 'test-app-id'],
                'new' => true
            ]
        ]);

        $psrRequest->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn($requestBody);
        $psrRequest->method('getHeaders')->willReturn([
            'SignatureCertChainUrl' => ['https://example.com/cert'],
            'Signature' => ['test-signature']
        ]);

        $response = $app->handlePsrRequest($psrRequest);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Test response', $response->response->outputSpeech->text);
    }
}

/**
 * Test handler for testing purposes
 */
class TestRequestHandler extends AbstractRequestHandler
{
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
        $response->response->outputSpeech = \Rboschin\AmazonAlexa\Response\OutputSpeech::createByText('Test response');
        $response->response->shouldEndSession = false;
        
        return $response;
    }
}
