<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Helper\SsmlGenerator;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Validation\RequestValidator;

require '../vendor/autoload.php';

/**
 * Complete Modern Skill Example
 * 
 * This example demonstrates all the new framework features:
 * - SkillApplication for simplified bootstrap
 * - IntentRouter for expressive handler registration
 * - ResponseBuilder for fluent response creation
 * - SsmlGenerator for complex speech
 * - Improved RequestValidator with configuration
 * - Proper error handling and logging
 */

// Configuration based on environment
$config = [
    'skill_id' => $_ENV['ALEXA_SKILL_ID'] ?? 'amzn1.ask.skill.modern-example',
    'debug_mode' => filter_var($_ENV['ALEXA_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'cert_cache_dir' => $_ENV['ALEXA_CERT_CACHE_DIR'] ?? null,
];

// Create improved RequestValidator with configuration
$validator = new RequestValidator(
    disableSignatureValidation: $config['debug_mode'],
    certCacheDir: $config['cert_cache_dir']
);

// Response helper for handlers that still use it (for compatibility)
$responseHelper = new ResponseHelper();

// Launch Handler with modern ResponseBuilder
$launchHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Modern way using ResponseBuilder
        return ResponseBuilder::create()
            ->text('Welcome to the modern skill example! You can ask me to tell a joke, start a quiz, or get help.')
            ->reprompt('What would you like to do?')
            ->sessionAttribute('launch_count', 1)
            ->keepSession()
            ->build();
    }
};

// Help Intent Handler
$helpHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'AMAZON.HelpIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $helpText = 'You can ask me to: ' .
            '"tell me a joke" - I\'ll tell you a funny joke, ' .
            '"start a quiz" - We\'ll play a trivia game, ' .
            '"what\'s my score" - I\'ll tell you your current score, or ' .
            '"help" - I\'ll repeat this message. ' .
            'What would you like to do?';
        
        return ResponseBuilder::create()
            ->text($helpText)
            ->reprompt('Which option would you like?')
            ->keepSession()
            ->build();
    }
};

// Joke Intent Handler with SSML
$jokeHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'TellJokeIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Using modern SsmlGenerator with fluent API
        $ssml = SsmlGenerator::create()
            ->say('Here\'s a joke for you!')
            ->pauseTime('1s')
            ->whisper('Why don\'t scientists trust atoms?')
            ->pauseTime('2s')
            ->emphasis('Because they make up everything!', 'strong')
            ->pauseTime('1s')
            ->say('Ha ha ha!')
            ->getSsml();
        
        return ResponseBuilder::create()
            ->ssml($ssml)
            ->reprompt('Want to hear another joke?')
            ->sessionAttribute('last_joke_time', time())
            ->keepSession()
            ->build();
    }
};

// Quiz Intent Handler with complex logic
$quizHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'StartQuizIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        // Initialize quiz session
        $questions = [
            'What is 2 + 2?',
            'What is the capital of France?',
            'What year did World War II end?',
            'Who wrote Romeo and Juliet?',
        ];
        
        $currentQuestion = $request->request->intent->slots['question']->value ?? 1;
        $questionText = $questions[$currentQuestion - 1] ?? $questions[0];
        
        // Use list helper for options
        $ssml = SsmlGenerator::create()
            ->say('Question ' . $currentQuestion . ':')
            ->pauseTime('500ms')
            ->emphasis($questionText, 'moderate')
            ->pauseTime('1s')
            ->list(['Option A: 10', 'Option B: 4', 'Option C: Paris', 'Option D: Shakespeare'], 'weak')
            ->pauseTime('2s')
            ->say('Choose your answer')
            ->getSsml();
        
        return ResponseBuilder::create()
            ->ssml($ssml)
            ->reprompt('Which option do you choose? A, B, C, or D?')
            ->sessionAttributes([
                'quiz_active' => true,
                'current_question' => $currentQuestion,
                'questions' => $questions,
                'score' => 0,
            ])
            ->keepSession()
            ->build();
    }
};

// Score Intent Handler
$scoreHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest && 
               $request->request->intent->name === 'GetScoreIntent';
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $session = $request->session ?? [];
        $score = $session->attributes['score'] ?? 0;
        
        $responseText = "Your current score is {$score} points.";
        
        if ($score >= 10) {
            $responseText .= " Congratulations! You're a quiz master!";
        }
        
        return ResponseBuilder::create()
            ->text($responseText)
            ->reprompt('Would you like to start a new quiz?')
            ->sessionAttribute('last_score_check', time())
            ->keepSession()
            ->build();
    }
};

// Fallback Handler for unmatched requests
$fallbackHandler = new class($responseHelper) extends \Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler {
    private \Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper;
    
    public function __construct(\Rboschin\AmazonAlexa\Helper\ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }
    
    public function supportsApplication(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return $request->context->system->application->applicationId === $config['skill_id'];
    }
    
    public function supportsRequest(\Rboschin\AmazonAlexa\Request\Request $request): bool
    {
        return true; // Always true for fallback
    }
    
    public function handleRequest(\Rboschin\AmazonAlexa\Request\Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        return ResponseBuilder::create()
            ->text('I\'m not sure how to handle that. You can ask me to tell a joke, start a quiz, get your score, or get help.')
            ->reprompt('What would you like to do?')
            ->keepSession()
            ->build();
    }
};

// Create IntentRouter with all handlers
$router = new IntentRouter();
$router->onLaunch($launchHandler)
       ->onIntent('AMAZON.HelpIntent', $helpHandler)
       ->onIntent('TellJokeIntent', $jokeHandler)
       ->onIntent('StartQuizIntent', $quizHandler)
       ->onIntent('GetScoreIntent', $scoreHandler)
       ->onFallback($fallbackHandler);

// Create adapter to use with SkillApplication
$registry = new IntentRouterAdapter($router);

// Create and run the skill application
$app = SkillApplication::fromGlobals(
    requestValidator: $validator,
    requestHandlerRegistry: $registry
);

try {
    $response = $app->handle();
    
    // Render response
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (\Exception $e) {
    // Enhanced error handling
    error_log("Skill Error: " . $e->getMessage());
    
    $errorResponse = ResponseBuilder::create()
        ->text('Sorry, I encountered an error. Please try again later.')
        ->endSession(true)
        ->build();
    
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
}

exit();
