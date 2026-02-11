<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;
use Rboschin\AmazonAlexa\Validation\RequestValidator;

require '../vendor/autoload.php';
require 'Handlers/SimpleIntentRequestHandler.php';

/**
 * Example showing improved RequestValidator features
 * 
 * This demonstrates:
 * - Custom certificate cache directory
 * - Disabled signature validation for development
 * - PSR-18 HTTP client support (if available)
 */

// Example 1: Default RequestValidator (backward compatible)
$defaultValidator = new RequestValidator();

// Example 2: Custom cache directory
$customCacheValidator = new RequestValidator(
    certCacheDir: '/tmp/alexa-certs'
);

// Example 3: Development mode with disabled signature validation
// WARNING: Use only in development/testing!
$devValidator = new RequestValidator(
    disableSignatureValidation: true
);

// Example 4: Custom timestamp tolerance
$customToleranceValidator = new RequestValidator(
    timestampTolerance: 300 // 5 minutes instead of 2.5
);

// Example 5: Using PSR-18 client (if available)
$psr18Validator = null;
if (interface_exists('Psr\Http\Client\ClientInterface')) {
    // This would work with any PSR-18 client
    // $psr18Client = new MyPsr18Client();
    // $psr18Validator = new RequestValidator(client: $psr18Client);
}

// Create response helper for handlers
$responseHelper = new ResponseHelper();

// Create a simple handler
$testHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
        return true;
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Get validator info for debugging
        $validatorInfo = '';
        if ($request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest) {
            $validatorInfo = 'Validator configured with: ';
            if ($request->getAttribute('validator_info')) {
                $validatorInfo .= $request->getAttribute('validator_info');
            }
        }
        
        return $this->responseHelper->respond(
            'Hello! This skill uses the improved RequestValidator. ' . $validatorInfo,
            false // Keep session open
        );
    }
};

// Create IntentRouter and register handlers
$router = new IntentRouter();
$router->onLaunch($testHandler)
       ->onFallback($testHandler);

// Create adapter to use with SkillApplication
$registry = new IntentRouterAdapter($router);

// Choose validator based on environment
$validator = match ($_ENV['APP_ENV'] ?? 'production') {
    'development' => $devValidator,
    'testing' => $customCacheValidator,
    default => $defaultValidator,
};

// Create skill application with chosen validator
$app = SkillApplication::fromGlobals(
    requestValidator: $validator,
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
