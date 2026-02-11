<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\RequestHandler;

use Rboschin\AmazonAlexa\Exception\MissingRequestHandlerException;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Request\Application;
use Rboschin\AmazonAlexa\Request\Context;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Request\Session;
use Rboschin\AmazonAlexa\Request\System;
use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use Rboschin\AmazonAlexa\Response\Response;
use PHPUnit\Framework\TestCase;

class RequestHandlerRegistryTest extends TestCase
{
    public function testSimpleRequest(): void
    {
        $responseHelper = new ResponseHelper();
        $handler = new SimpleTestRequestHandler($responseHelper);
        $registry = new RequestHandlerRegistry();

        $intentRequest = new IntentRequest();
        $intentRequest->type = 'test';
        $application = new Application();
        $application->applicationId = 'my_amazon_skill_id';
        $system = new System();
        $system->application = $application;
        $context = new Context();
        $context->system = $system;
        $session = new Session();
        $session->application = $application;
        $request = new Request();
        $request->request = $intentRequest;
        $request->context = $context;
        $request->session = $session;

        $registry->addHandler($handler);
        $registry->getSupportingHandler($request);

        $this->assertSame($handler, $registry->getSupportingHandler($request));
    }

    public function testSimpleRequestAddHandlerByConstructor(): void
    {
        $responseHelper = new ResponseHelper();
        $handler = new SimpleTestRequestHandler($responseHelper);
        $registry = new RequestHandlerRegistry([$handler]);

        $intentRequest = new IntentRequest();
        $intentRequest->type = 'test';
        $application = new Application();
        $application->applicationId = 'my_amazon_skill_id';
        $system = new System();
        $system->application = $application;
        $context = new Context();
        $context->system = $system;
        $session = new Session();
        $session->application = $application;
        $request = new Request();
        $request->request = $intentRequest;
        $request->context = $context;
        $request->session = $session;

        $registry->getSupportingHandler($request);

        $this->assertSame($handler, $registry->getSupportingHandler($request));
    }

    public function testMissingHandlerRequest(): void
    {
        $registry = new RequestHandlerRegistry();

        $intentRequest = new IntentRequest();
        $intentRequest->type = 'test';
        $application = new Application();
        $application->applicationId = 'my_amazon_skill_id';
        $system = new System();
        $system->application = $application;
        $context = new Context();
        $context->system = $system;
        $session = new Session();
        $session->application = $application;
        $request = new Request();
        $request->request = $intentRequest;
        $request->context = $context;
        $request->session = $session;

        $this->expectException(MissingRequestHandlerException::class);
        $registry->getSupportingHandler($request);
    }
}
class SimpleTestRequestHandler extends AbstractRequestHandler
{
    public function __construct(
        private readonly ResponseHelper $responseHelper
    ) {
        $this->supportedApplicationIds = ['my_amazon_skill_id'];
    }

    public function supportsRequest(Request $request): bool
    {
        return 'test' === $request->request->type;
    }

    public function handleRequest(Request $request): Response
    {
        return $this->responseHelper->respond('Success :)');
    }
}
