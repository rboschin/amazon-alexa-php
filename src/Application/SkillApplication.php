<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Application;

use Rboschin\AmazonAlexa\Exception\MissingRequestHandlerException;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use Rboschin\AmazonAlexa\Response\OutputSpeech;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Validation\RequestValidator;
use Rboschin\AmazonAlexa\Config\LoggerConfig;
use Rboschin\AmazonAlexa\Services\PerformanceService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SkillApplication provides a centralized kernel for Alexa skills.
 * 
 * This class encapsulates the bootstrap workflow:
 * - Request parsing from Amazon format
 * - Request validation 
 * - Handler selection and invocation
 * - Error handling
 * 
 * Usage:
 * ```php
 * $app = SkillApplication::fromGlobals();
 * $response = $app->handle();
 * echo json_encode($response);
 * ```
 */
class SkillApplication
{
    public function __construct(
        private RequestValidator $requestValidator,
        private RequestHandlerRegistry $requestHandlerRegistry,
    ) {
    }

    /**
     * Create instance from global PHP variables (php://input and $_SERVER)
     */
    public static function fromGlobals(
        ?RequestValidator $requestValidator = null,
        ?RequestHandlerRegistry $requestHandlerRegistry = null,
    ): self {
        $requestBody = file_get_contents('php://input');
        $headers = self::getHeadersFromGlobals();
        
        return new self(
            $requestValidator ?? new RequestValidator(),
            $requestHandlerRegistry ?? new RequestHandlerRegistry(),
        );
    }

    /**
     * Handle request from raw body and headers array
     */
    public function handleRaw(string $requestBody, array $headers): Response
    {
        try {
            // Parse request
            $certUrl = $headers['HTTP_SIGNATURECERTCHAINURL'] ?? '';
            $signature = $headers['HTTP_SIGNATURE'] ?? '';
            
            $request = Request::fromAmazonRequest($requestBody, $certUrl, $signature);
            
            // Validate request
            $this->requestValidator->validate($request);
            
            // Handle request
            return $this->handleRequest($request);
            
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle request from PSR-7 ServerRequestInterface
     */
    public function handlePsrRequest(ServerRequestInterface $request): Response
    {
        $requestBody = (string) $request->getBody();
        $headers = $request->getHeaders();
        
        LoggerConfig::info("Processing PSR-7 request", [
            'content_length' => strlen($requestBody),
            'headers_count' => count($headers)
        ]);
        
        PerformanceService::startTimer('psr_request_processing');
        
        // Convert PSR-7 headers to simple array format
        $simpleHeaders = [];
        foreach ($headers as $name => $values) {
            $simpleHeaders['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $values[0] ?? '';
        }
        
        $response = $this->handleRaw($requestBody, $simpleHeaders);
        
        PerformanceService::endTimer('psr_request_processing');
        
        return $response;
    }

    /**
     * Handle request using current globals (convenience method)
     */
    public function handle(): Response
    {
        $requestBody = file_get_contents('php://input');
        $headers = self::getHeadersFromGlobals();
        
        LoggerConfig::info("Processing request from globals", [
            'content_length' => strlen($requestBody),
            'headers_count' => count($headers)
        ]);
        
        PerformanceService::startTimer('request_processing');
        
        $response = $this->handleRaw($requestBody, $headers);
        
        PerformanceService::endTimer('request_processing');
        
        return $response;
    }

    /**
     * Process the request through the handler registry
     */
    private function handleRequest(Request $request): Response
    {
        $requestType = $request->getRequestType();
        $intentName = $request->getIntentName() ?? 'unknown';
        $userId = $request->getUserId() ?? 'unknown';
        
        LoggerConfig::info("Processing request", [
            'request_type' => $requestType,
            'intent' => $intentName,
            'user_id' => $userId
        ]);
        
        PerformanceService::startTimer('handler_selection');
        
        try {
            $handler = $this->requestHandlerRegistry->getSupportingHandler($request);
            
            PerformanceService::endTimer('handler_selection', [
                'handler_found' => true,
                'handler_class' => get_class($handler)
            ]);
            
            LoggerConfig::debug("Handler selected", [
                'handler' => get_class($handler),
                'intent' => $intentName
            ]);
            
            return $handler->handleRequest($request);
            
        } catch (MissingRequestHandlerException $e) {
            PerformanceService::endTimer('handler_selection', [
                'handler_found' => false,
                'error' => $e->getMessage()
            ]);
            
            LoggerConfig::warning("No handler found for request", [
                'request_type' => $requestType,
                'intent' => $intentName,
                'error' => $e->getMessage()
            ]);
            
            // Return a generic error response when no handler is found
            return $this->createErrorResponse('Sorry, I cannot handle this request right now.');
        }
    }

    /**
     * Handle exceptions and return appropriate error response
     */
    private function handleError(\Exception $e): Response
    {
        LoggerConfig::error("Application error occurred", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        PerformanceService::incrementCounter('application_errors');
        
        // Return user-friendly error response
        return $this->createErrorResponse('Sorry, something went wrong. Please try again later.');
    }

    /**
     * Create a basic error response
     */
    private function createErrorResponse(string $message): Response
    {
        $response = new Response();
        $response->response->outputSpeech = OutputSpeech::createByText($message);
        $response->response->shouldEndSession = true;
        
        return $response;
    }

    /**
     * Extract headers from $_SERVER in the same format as the original example
     */
    private static function getHeadersFromGlobals(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
}
