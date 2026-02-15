<?php

declare(strict_types=1);

namespace TombolaNapoletana\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Response;

class SimpleLaunchHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        return true; // Accept all applications for testing
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
    }

    public function handleRequest(Request $request): Response
    {
        return ResponseBuilder::create()
            ->text("Simple test response")
            ->reprompt("What would you like to do?")
            ->build();
    }
}
