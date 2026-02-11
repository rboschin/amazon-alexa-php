<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;
use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;

require '../vendor/autoload.php';
require 'Handlers/SimpleIntentRequestHandler.php';

/**
 * Example showing ResponseBuilder fluent API usage
 * 
 * This demonstrates the new fluent response building capabilities:
 * - Chain methods for expressive response creation
 * - Convenience static methods for common patterns
 * - Integration with existing handlers
 */

// Create response helper for handlers
$responseHelper = new ResponseHelper();

// Create handlers using ResponseBuilder internally
$helpRequestHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
        return $request->request->type === 'LaunchRequest' || 
               ($request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
                $request->request->intent->name === 'AMAZON.HelpIntent');
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 1: Using fluent ResponseBuilder directly
        $card = new Card(
            type: Card::TYPE_SIMPLE,
            title: 'Help',
            content: 'Welcome to the skill! You can say "hello" or ask for help.'
        );
        
        return ResponseBuilder::create()
            ->text('Welcome to my skill! You can say hello or ask for help anytime.')
            ->reprompt('What would you like to do?')
            ->card($card)
            ->sessionAttribute('helpShown', 'true')
            ->keepSession()
            ->build();
    }
};

$helloIntentHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'HelloIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 2: Using convenience static methods
        return ResponseBuilder::respondAndKeepSession('Hello! Nice to meet you!');
    }
};

$goodbyeIntentHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'GoodbyeIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 3: Using SSML with convenience method
        return ResponseBuilder::respondSsmlAndEndSession(
            '<speak>Goodbye! <emphasis level="strong">Thanks for using my skill!</emphasis></speak>'
        );
    }
};

$questionIntentHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'QuestionIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 4: Using ask convenience method
        return ResponseBuilder::ask(
            'I can help you with various things. What would you like to know?',
            'You can ask me about the weather, time, or just say hello.'
        );
    }
};

// Create IntentRouter and register handlers
$router = new IntentRouter();
$router->onIntent('HelloIntent', $helloIntentHandler)
       ->onIntent('GoodbyeIntent', $goodbyeIntentHandler)
       ->onIntent('QuestionIntent', $questionIntentHandler)
       ->onIntent('AMAZON.HelpIntent', $helpRequestHandler)
       ->onLaunch($helpRequestHandler);

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
    // Fallback error handling using ResponseBuilder
    $errorResponse = ResponseBuilder::respondAndEndSession(
        'Sorry, something went wrong. Please try again later.'
    );
    
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
}

exit();
