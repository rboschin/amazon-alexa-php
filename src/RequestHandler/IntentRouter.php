<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\RequestHandler;

use Rboschin\AmazonAlexa\Exception\MissingRequestHandlerException;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\SessionEndedRequest;

/**
 * IntentRouter provides a simplified way to register and route handlers for different request types.
 * 
 * This class offers a more expressive API compared to the basic RequestHandlerRegistry:
 * - Register handlers by intent name
 * - Register handlers for specific request types (Launch, SessionEnded, etc.)
 * - Support for fallback handlers
 * 
 * Usage:
 * ```php
 * $router = new IntentRouter();
 * $router->onIntent('MyIntent', $handler)
 *        ->onLaunch($launchHandler)
 *        ->onFallback($fallbackHandler);
 * ```
 */
class IntentRouter
{
    /** @var array<string, AbstractRequestHandler> */
    private array $intentHandlers = [];

    private ?AbstractRequestHandler $launchHandler = null;
    private ?AbstractRequestHandler $sessionEndedHandler = null;
    private ?AbstractRequestHandler $fallbackHandler = null;

    /**
     * Register a handler for a specific intent name
     */
    public function onIntent(string $intentName, AbstractRequestHandler $handler): self
    {
        $this->intentHandlers[$intentName] = $handler;
        return $this;
    }

    /**
     * Register a handler for LaunchRequest
     */
    public function onLaunch(AbstractRequestHandler $handler): self
    {
        $this->launchHandler = $handler;
        return $this;
    }

    /**
     * Register a handler for SessionEndedRequest
     */
    public function onSessionEnded(AbstractRequestHandler $handler): self
    {
        $this->sessionEndedHandler = $handler;
        return $this;
    }

    /**
     * Register a fallback handler for unmatched requests
     */
    public function onFallback(AbstractRequestHandler $handler): self
    {
        $this->fallbackHandler = $handler;
        return $this;
    }

    /**
     * Get the appropriate handler for the given request
     */
    public function getHandlerFor(Request $request): AbstractRequestHandler
    {
        $handler = $this->findHandler($request);
        
        if ($handler === null) {
            throw new MissingRequestHandlerException('No handler found for request');
        }

        return $handler;
    }

    /**
     * Find the appropriate handler for the request
     */
    private function findHandler(Request $request): ?AbstractRequestHandler
    {
        // Handle IntentRequest
        if ($request->request instanceof IntentRequest) {
            $intentName = $request->request->intent?->name;
            
            if ($intentName && isset($this->intentHandlers[$intentName])) {
                return $this->intentHandlers[$intentName];
            }
        }

        // Handle LaunchRequest
        if ($request->request instanceof LaunchRequest && $this->launchHandler) {
            return $this->launchHandler;
        }

        // Handle SessionEndedRequest
        if ($request->request instanceof SessionEndedRequest && $this->sessionEndedHandler) {
            return $this->sessionEndedHandler;
        }

        // Return fallback handler if available
        return $this->fallbackHandler;
    }

    /**
     * Check if there's a handler available for the request
     */
    public function hasHandlerFor(Request $request): bool
    {
        return $this->findHandler($request) !== null;
    }

    /**
     * Get all registered intent names
     * 
     * @return string[]
     */
    public function getRegisteredIntents(): array
    {
        return array_keys($this->intentHandlers);
    }

    /**
     * Check if an intent is registered
     */
    public function hasIntent(string $intentName): bool
    {
        return isset($this->intentHandlers[$intentName]);
    }

    /**
     * Remove an intent handler
     */
    public function removeIntent(string $intentName): self
    {
        unset($this->intentHandlers[$intentName]);
        return $this;
    }

    /**
     * Clear all handlers
     */
    public function clear(): self
    {
        $this->intentHandlers = [];
        $this->launchHandler = null;
        $this->sessionEndedHandler = null;
        $this->fallbackHandler = null;
        return $this;
    }
}
