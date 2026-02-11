<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Helper\SsmlGenerator;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;

require '../vendor/autoload.php';

/**
 * Example showing improved SsmlGenerator with fluent API
 * 
 * This demonstrates:
 * - Factory method for creation
 * - Fluent method chaining
 * - Helper methods for common patterns
 * - Backward compatibility with existing methods
 */

// Create response helper for handlers
$responseHelper = new ResponseHelper();

// Create handlers that demonstrate different SSML patterns
$numberHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               $request->request->intent->name === 'NumberIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $number = $request->request->intent->slots['number']->value ?? 42;
        
        // Example 1: Using fluent API with factory
        $ssml = SsmlGenerator::create()
            ->say('The number is')
            ->pauseTime('500ms')
            ->number((int)$number)
            ->pauseStrength('medium')
            ->whisper('Thank you for asking!')
            ->getSsml();
        
        return $this->responseHelper->respondSsml($ssml, false);
    }
};

$listHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               $request->request->intent->name === 'ListIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $items = ['apples', 'bananas', 'oranges', 'grapes'];
        
        // Example 2: Using list helper with automatic pauses
        $ssml = SsmlGenerator::create()
            ->say('Your shopping list contains:')
            ->pauseTime('1s')
            ->list($items, 'weak')
            ->pauseTime('1s')
            ->say('That\'s all items!')
            ->getSsml();
        
        return $this->responseHelper->respondSsml($ssml, false);
    }
};

$countdownHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               $request->request->intent->name === 'CountdownIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 3: Using countdown helper
        $ssml = SsmlGenerator::create()
            ->say('Starting countdown')
            ->pauseTime('1s')
            ->countdown(5, 1)
            ->pauseTime('500ms')
            ->emphasis('Done!', 'strong')
            ->getSsml();
        
        return $this->responseHelper->respondSsml($ssml, false);
    }
};

$spellHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               $request->request->intent->name === 'SpellIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $word = $request->request->intent->slots['word']->value ?? 'hello';
        
        // Example 4: Using spell helper
        $ssml = SsmlGenerator::create()
            ->say('I will spell the word')
            ->pauseTime('500ms')
            ->spell($word)
            ->pauseTime('500ms')
            ->say('That spells ' . $word . '!')
            ->getSsml();
        
        return $this->responseHelper->respondSsml($ssml, false);
    }
};

$complexHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               $request->request->intent->name === 'ComplexIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Example 5: Complex SSML with multiple features
        $ssml = SsmlGenerator::create()
            ->say('Welcome to the advanced SSML demo!')
            ->pauseTime('1s')
            ->paragraph('This demonstrates various SSML features:')
            ->pauseTime('800ms')
            ->say('First')
            ->pauseStrength('weak')
            ->emphasis('ordinal numbers', 'moderate')
            ->pauseTime('500ms')
            ->ordinal(1)
            ->pauseTime('300ms')
            ->say('Second')
            ->pauseStrength('weak')
            ->digits('23')
            ->pauseTime('500ms')
            ->say('Third')
            ->pauseStrength('weak')
            ->sayWithAmazonEffect('whispered text', 'whispered')
            ->pauseTime('1s')
            ->pronounceInLanguage('en-US', 'Hello in English')
            ->pauseTime('500ms')
            ->sayWithVoice('Matthew', 'This uses a specific voice')
            ->getSsml();
        
        return $this->responseHelper->respondSsml($ssml, false);
    }
};

// Create IntentRouter and register handlers
$router = new IntentRouter();
$router->onIntent('NumberIntent', $numberHandler)
       ->onIntent('ListIntent', $listHandler)
       ->onIntent('CountdownIntent', $countdownHandler)
       ->onIntent('SpellIntent', $spellHandler)
       ->onIntent('ComplexIntent', $complexHandler)
       ->onFallback(new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
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
               return $this->responseHelper->respond(
                   'Try asking for a number, list, countdown, spell, or complex demo. ' .
                   'For example: "tell me the number 42" or "spell hello"',
                   false
               );
           }
       });

// Create adapter to use with SkillApplication
$registry = new IntentRouterAdapter($router);

// Create skill application
$app = SkillApplication::fromGlobals(
    requestHandlerRegistry: $registry
);

// Handle request
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
