<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\RequestHandler;

use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Config\LoggerConfig;
use Rboschin\AmazonAlexa\Services\PerformanceService;

abstract class AbstractRequestHandler
{
    /**
     * @param string[] $supportedApplicationIds Array of supported application IDs
     */
    public function __construct(
        protected array $supportedApplicationIds = [],
    ) {
    }

    public function supportsApplication(Request $request): bool
    {
        $appId = $request->getApplicationId();
        $supported = in_array($appId, $this->supportedApplicationIds, true);
        
        LoggerConfig::debug("Application support check", [
            'app_id' => $appId,
            'supported' => $supported,
            'handler' => static::class
        ]);
        
        return $supported;
    }

    abstract public function supportsRequest(Request $request): bool;

    public function handleRequest(Request $request): ?Response
    {
        $handlerClass = static::class;
        $requestType = $request->getRequestType();
        $intentName = $request->getIntentName() ?? 'unknown';
        
        LoggerConfig::info("Handling request", [
            'handler' => $handlerClass,
            'request_type' => $requestType,
            'intent' => $intentName,
            'user_id' => $request->getUserId() ?? 'unknown'
        ]);
        
        PerformanceService::startTimer("handler_{$handlerClass}");
        
        try {
            $response = $this->handleRequestInternal($request);
            
            PerformanceService::endTimer("handler_{$handlerClass}", [
                'intent' => $intentName,
                'response_type' => $response ? get_class($response) : 'null'
            ]);
            
            LoggerConfig::info("Request handled successfully", [
                'handler' => $handlerClass,
                'intent' => $intentName
            ]);
            
            return $response;
            
        } catch (\Throwable $e) {
            PerformanceService::endTimer("handler_{$handlerClass}", [
                'intent' => $intentName,
                'error' => $e->getMessage(),
                'success' => false
            ]);
            
            LoggerConfig::error("Request handling failed", [
                'handler' => $handlerClass,
                'intent' => $intentName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Internal method to be implemented by concrete handlers
     */
    abstract protected function handleRequestInternal(Request $request): ?Response;
    
    /**
     * Log performance metrics for this handler
     */
    protected function logPerformance(string $operation, array $metrics): void
    {
        LoggerConfig::performance($operation, array_merge([
            'handler' => static::class
        ], $metrics));
    }
    
    /**
     * Log debug information specific to this handler
     */
    protected function logDebug(string $message, array $context = []): void
    {
        LoggerConfig::debug($message, array_merge([
            'handler' => static::class
        ], $context));
    }
    
    /**
     * Log error information specific to this handler
     */
    protected function logError(string $message, array $context = []): void
    {
        LoggerConfig::error($message, array_merge([
            'handler' => static::class
        ], $context));
    }
}
