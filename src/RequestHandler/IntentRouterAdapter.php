<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\RequestHandler;

use Rboschin\AmazonAlexa\Exception\MissingRequestHandlerException;
use Rboschin\AmazonAlexa\Request\Request;

/**
 * Adapter to make IntentRouter compatible with RequestHandlerRegistry interface
 * 
 * This allows IntentRouter to be used as a drop-in replacement for RequestHandlerRegistry
 * while maintaining backward compatibility.
 */
class IntentRouterAdapter extends RequestHandlerRegistry
{
    public function __construct(private IntentRouter $intentRouter)
    {
        // Parent constructor is called with empty array since we use IntentRouter
        parent::__construct([]);
    }

    public function getSupportingHandler(Request $request): AbstractRequestHandler
    {
        try {
            return $this->intentRouter->getHandlerFor($request);
        } catch (MissingRequestHandlerException $e) {
            // Re-throw to maintain the same interface
            throw $e;
        }
    }

    /**
     * Get the underlying IntentRouter for advanced operations
     */
    public function getIntentRouter(): IntentRouter
    {
        return $this->intentRouter;
    }

    /**
     * Forward method calls to IntentRouter for fluent interface
     */
    public function onIntent(string $intentName, AbstractRequestHandler $handler): self
    {
        $this->intentRouter->onIntent($intentName, $handler);
        return $this;
    }

    public function onLaunch(AbstractRequestHandler $handler): self
    {
        $this->intentRouter->onLaunch($handler);
        return $this;
    }

    public function onSessionEnded(AbstractRequestHandler $handler): self
    {
        $this->intentRouter->onSessionEnded($handler);
        return $this;
    }

    public function onFallback(AbstractRequestHandler $handler): self
    {
        $this->intentRouter->onFallback($handler);
        return $this;
    }
}
