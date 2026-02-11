<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\RequestHandler\Basic\HelpRequestHandler;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;

require '../vendor/autoload.php';
require 'Handlers/SimpleIntentRequestHandler.php';

/**
 * Example showing IntentRouter usage with SkillApplication
 * 
 * This demonstrates the simplified registration API:
 * - Register handlers by intent name
 * - Register handlers for specific request types
 * - Use fallback handler for unmatched requests
 */

// Create response helper for handlers
$responseHelper = new ResponseHelper();

// Create handlers
$helpRequestHandler = new HelpRequestHandler($responseHelper, 'Help Text', ['my_amazon_skill_id']);
$mySimpleRequestHandler = new SimpleIntentRequestHandler($responseHelper);

// Create a simple fallback handler
$fallbackHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return true;
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return true; // This is our fallback
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        return $this->responseHelper->respond("I'm not sure how to handle that request. Please try asking for help.");
    }
};

// Create IntentRouter and register handlers
$router = new IntentRouter();
$router->onIntent('SimpleIntent', $mySimpleRequestHandler)
       ->onIntent('AMAZON.HelpIntent', $helpRequestHandler)
       ->onLaunch($helpRequestHandler) // Use help handler for launch too
       ->onFallback($fallbackHandler);

// Create adapter to use with SkillApplication
$registry = new IntentRouterAdapter($router);

// Create skill application
$app = SkillApplication::fromGlobals(
    requestHandlerRegistry: $registry
);

// Handle the request
try {
    $response = $app->handle();
    
    // Render response
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (\Exception $e) {
    // Fallback error handling
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

exit();
