<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\TestSupport;

use Rboschin\AmazonAlexa\Intent\Intent;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
use Rboschin\AmazonAlexa\Request\Request\Standard\SessionEndedRequest;

/**
 * Factory for creating test requests for Alexa skills
 * 
 * This utility simplifies creating various types of requests
 * for unit and integration testing.
 */
class IntentRequestFactory
{
    /**
     * Create a basic IntentRequest
     */
    public static function forIntent(string $intentName, array $slots = []): Request
    {
        $intent = new Intent();
        $intent->name = $intentName;
        
        // Add slots
        foreach ($slots as $name => $value) {
            $slot = new \Rboschin\AmazonAlexaIntent\Slot();
            $slot->name = $name;
            $slot->value = $value;
            $intent->slots[] = $slot;
        }
        
        $intentRequest = new IntentRequest(
            requestId: 'test-request-' . uniqid(),
            timestamp: new \DateTime(),
            locale: 'en-US',
            intent: $intent
        );
        
        return new Request(
            version: '1.0',
            request: $intentRequest,
            amazonRequestBody: json_encode(['request' => ['intent' => ['name' => $intentName]]]),
            signatureCertChainUrl: 'https://s3.amazonaws.com/echo.api/test',
            signature: 'test-signature'
        );
    }

    /**
     * Create an IntentRequest with slot confirmation
     */
    public static function forIntentWithConfirmation(string $intentName, array $slots = []): Request
    {
        $intent = new Intent();
        $intent->name = $intentName;
        $intent->confirmationStatus = 'CONFIRMED';
        
        // Add slots
        foreach ($slots as $name => $value) {
            $slot = new \Rboschin\AmazonAlexaIntent\Slot();
            $slot->name = $name;
            $slot->value = $value;
            $slot->confirmationStatus = 'CONFIRMED';
            $intent->slots[] = $slot;
        }
        
        $intentRequest = new IntentRequest(
            requestId: 'test-request-' . uniqid(),
            timestamp: new \DateTime(),
            locale: 'en-US',
            intent: $intent
        );
        
        return new Request(
            version: '1.0',
            request: $intentRequest,
            amazonRequestBody: json_encode(['request' => ['intent' => ['name' => $intentName]]]),
            signatureCertChainUrl: 'https://s3.amazonaws.com/echo.api/test',
            signature: 'test-signature'
        );
    }

    /**
     * Create a LaunchRequest
     */
    public static function forLaunch(): Request
    {
        $launchRequest = new LaunchRequest(
            requestId: 'test-launch-' . uniqid(),
            timestamp: new \DateTime(),
            locale: 'en-US'
        );
        
        return new Request(
            version: '1.0',
            request: $launchRequest,
            amazonRequestBody: json_encode(['request' => ['type' => 'LaunchRequest']]),
            signatureCertChainUrl: 'https://s3.amazonaws.com/echo.api/test',
            signature: 'test-signature'
        );
    }

    /**
     * Create a SessionEndedRequest
     */
    public static function forSessionEnded(string $reason = 'USER_INITIATED'): Request
    {
        $sessionEndedRequest = new SessionEndedRequest(
            requestId: 'test-session-ended-' . uniqid(),
            timestamp: new \DateTime(),
            locale: 'en-US',
            reason: $reason
        );
        
        return new Request(
            version: '1.0',
            request: $sessionEndedRequest,
            amazonRequestBody: json_encode(['request' => ['type' => 'SessionEndedRequest']]),
            signatureCertChainUrl: 'https://s3.amazonaws.com/echo.api/test',
            signature: 'test-signature'
        );
    }

    /**
     * Create an IntentRequest with dialog state
     */
    public static function forIntentWithDialogState(string $intentName, string $dialogState, array $slots = []): Request
    {
        $intent = new Intent();
        $intent->name = $intentName;
        
        // Add slots
        foreach ($slots as $name => $value) {
            $slot = new \Rboschin\AmazonAlexaIntent\Slot();
            $slot->name = $name;
            $slot->value = $value;
            $intent->slots[] = $slot;
        }
        
        $intentRequest = new IntentRequest(
            requestId: 'test-request-' . uniqid(),
            timestamp: new \DateTime(),
            locale: 'en-US',
            dialogState: $dialogState,
            intent: $intent
        );
        
        return new Request(
            version: '1.0',
            request: $intentRequest,
            amazonRequestBody: json_encode(['request' => ['intent' => ['name' => $intentName]]]),
            signatureCertChainUrl: 'https://s3.amazonaws.com/echo.api/test',
            signature: 'test-signature'
        );
    }

    /**
     * Create a request with custom session attributes
     */
    public static function withSessionAttributes(Request $request, array $attributes): Request
    {
        // Create a new request with modified session
        $newRequest = clone $request;
        $newRequest->session->attributes = array_merge($request->session->attributes ?? [], $attributes);
        
        return $newRequest;
    }

    /**
     * Create a request for testing error handling
     */
    public static function forErrorTesting(): Request
    {
        $intentRequest = new IntentRequest(
            requestId: 'test-error-' . uniqid(),
            timestamp: new \DateTime('-150 seconds'), // Invalid timestamp
            locale: 'en-US'
        );
        
        $intent = new Intent();
        $intent->name = 'TestIntent';
        $intentRequest->intent = $intent;
        
        return new Request(
            version: '1.0',
            request: $intentRequest,
            amazonRequestBody: json_encode(['request' => ['intent' => ['name' => 'TestIntent']]]),
            signatureCertChainUrl: 'invalid-url', // Invalid cert URL
            signature: 'invalid-signature'
        );
    }

    /**
     * Create multiple requests for batch testing
     */
    public static function batch(array $intents): array
    {
        $requests = [];
        
        foreach ($intents as $intentData) {
            $name = $intentData['name'] ?? 'TestIntent';
            $slots = $intentData['slots'] ?? [];
            
            $requests[] = self::forIntent($name, $slots);
        }
        
        return $requests;
    }

    /**
     * Create a request with APL context
     */
    public static function forIntentWithApl(string $intentName, array $slots = [], array $aplContext = []): Request
    {
        $request = self::forIntent($intentName, $slots);
        
        // Add APL context to the request
        if (!empty($aplContext)) {
            $request->context->viewport = new \Rboschin\AmazonAlexaRequest\Context();
            $request->context->viewport->shape = 'RECTANGLE';
            $request->context->viewport->dpi = 160;
            $request->context->viewport->pixelWidth = 1024;
            $request->context->viewport->pixelHeight = 600;
            
            // Add APL interface if available
            if (class_exists(\Rboschin\AmazonAlexaRequest\AlexaPresentationAPL::class)) {
                $request->context->alexaPresentationAPL = new \Rboschin\AmazonAlexaRequest\AlexaPresentationAPL();
                $request->context->alexaPresentationAPL->runtime = new \Rboschin\AmazonAlexaRequest\AlexaPresentationAPL\Runtime();
                $request->context->alexaPresentationAPL->runtime->maxVersion = '1.8';
            }
        }
        
        return $request;
    }
}
