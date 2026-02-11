<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\RequestHandler\Basic\HelpRequestHandler;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;

require '../vendor/autoload.php';
require 'Handlers/SimpleIntentRequestHandler.php';

/**
 * Modern example using SkillApplication kernel
 * 
 * This shows how the SkillApplication reduces boilerplate:
 * - No manual request parsing
 * - No manual validation 
 * - No manual handler registry iteration
 * - Built-in error handling
 */

// Create response helper for handlers
$responseHelper = new ResponseHelper();

// Create handlers
$helpRequestHandler = new HelpRequestHandler($responseHelper, 'Help Text', ['my_amazon_skill_id']);
$mySimpleRequestHandler = new SimpleIntentRequestHandler($responseHelper);

// Create handler registry
$requestHandlerRegistry = new RequestHandlerRegistry([$helpRequestHandler, $mySimpleRequestHandler]);

// Create skill application
$app = SkillApplication::fromGlobals(
    requestHandlerRegistry: $requestHandlerRegistry
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
