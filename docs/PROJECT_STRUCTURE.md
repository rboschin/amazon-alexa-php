# Project Structure Guidelines

This document provides recommended structure and best practices for organizing Alexa skills built with this framework.

## Recommended Directory Structure

```
my-alexa-skill/
├── src/
│   ├── Handlers/                    # Your intent handlers
│   │   ├── LaunchHandler.php
│   │   ├── HelpHandler.php
│   │   └── CustomIntentHandler.php
│   ├── Domain/                      # Business logic and services
│   │   ├── Services/
│   │   └── Models/
│   └── Config/                      # Configuration files
├── tests/
│   ├── Unit/
│   └── Integration/
├── public/
│   └── index.php                    # Entry point
├── composer.json
└── README.md
```

## Handler Organization

### 1. Use SkillApplication for Bootstrap

**Recommended:**
```php
<?php
require 'vendor/autoload.php';

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;

// Create router and register handlers
$router = new IntentRouter();
$router->onIntent('MyIntent', new MyIntentHandler())
       ->onLaunch(new LaunchHandler())
       ->onFallback(new FallbackHandler());

// Create and run application
$app = SkillApplication::fromGlobals(
    requestHandlerRegistry: new IntentRouterAdapter($router)
);

$response = $app->handle();
echo json_encode($response);
```

**Avoid (old way):**
```php
// Manual bootstrap - not recommended
$requestBody = file_get_contents('php://input');
$alexaRequest = Request::fromAmazonRequest($requestBody, $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);
$validator = new RequestValidator();
$validator->validate($alexaRequest);
// ... more boilerplate
```

### 2. Handler Best Practices

```php
<?php
namespace App\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;

class MyIntentHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        return true; // Or check your skill ID
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof IntentRequest 
            && $request->request->intent->name === 'MyIntent';
    }

    public function handleRequest(Request $request): Response
    {
        $slotValue = $request->request->intent->slots['mySlot']->value ?? null;
        
        return ResponseBuilder::create()
            ->text("You said: {$slotValue}")
            ->reprompt("What else would you like to know?")
            ->keepSession()
            ->build();
    }
}
```

### 3. Use ResponseBuilder for Responses

**Recommended:**
```php
return ResponseBuilder::create()
    ->text('Welcome!')
    ->reprompt('How can I help you?')
    ->keepSession()
    ->build();
```

**Avoid (old way):**
```php
$responseHelper = new ResponseHelper();
$responseHelper->respond('Welcome!');
$responseHelper->reprompt('How can I help you?');
return $responseHelper->getResponse();
```

### 4. Use SsmlGenerator for Complex Speech

**Recommended:**
```php
$ssml = SsmlGenerator::create()
    ->say('Welcome to the game!')
    ->pauseTime('1s')
    ->emphasis('Get ready', 'strong')
    ->pauseTime('500ms')
    ->countdown(3, 1)
    ->getSsml();

return ResponseBuilder::create()
    ->ssml($ssml)
    ->keepSession()
    ->build();
```

## Domain Logic Organization

### Services
```php
<?php
namespace App\Domain\Services;

class GameService
{
    public function calculateScore(array $answers): int
    {
        // Business logic here
        return array_sum($answers);
    }
    
    public function isGameOver(int $currentScore): bool
    {
        return $currentScore >= 100;
    }
}
```

### Models
```php
<?php
namespace App\Domain\Models;

class GameState
{
    public function __construct(
        public int $score = 0,
        public int $round = 1,
        public array $history = []
    ) {
    }
    
    public function addAnswer(int $answer): void
    {
        $this->history[] = $answer;
        $this->score += $answer;
    }
}
```

## Configuration

### Environment-Based Configuration
```php
<?php
// config/alexa.php
return [
    'skill_id' => $_ENV['ALEXA_SKILL_ID'] ?? 'amzn1.ask.skill.my-skill',
    'debug_mode' => $_ENV['ALEXA_DEBUG'] ?? false,
    'cert_cache_dir' => $_ENV['ALEXA_CERT_CACHE_DIR'] ?? null,
];
```

### Usage in Handlers
```php
<?php
$config = require 'config/alexa.php';

$validator = new RequestValidator(
    disableSignatureValidation: $config['debug_mode'],
    certCacheDir: $config['cert_cache_dir']
);
```

## Testing Structure

### Unit Tests
```php
<?php
namespace Tests\Unit;

use Rboschin\AmazonAlexa\Test\Helper\SsmlGeneratorTest;

class MyHandlerTest extends TestCase
{
    public function testHandlesMyIntent(): void
    {
        $request = $this->createIntentRequest('MyIntent', ['slot' => 'value']);
        $handler = new MyIntentHandler();
        
        $this->assertTrue($Handler->supportsRequest($request));
    }
    
    public function testReturnsCorrectResponse(): void
    {
        $request = $this->createIntentRequest('MyIntent', ['slot' => 'value']);
        $Handler = new MyIntentHandler();
        $response = $Handler->handleRequest($request);
        
        $this->assertStringContains('You said: value', $response->response->outputSpeech->text);
    }
}
```

### Integration Tests
```php
<?php
namespace Tests\Integration;

use Rboschin\AmazonAlexa\Application\SkillApplication;

class SkillIntegrationTest extends TestCase
{
    public function testCompleteRequestFlow(): void
    {
        $requestBody = json_encode([
            'version' => '1.0',
            'request' => [
                'type' => 'LaunchRequest',
                'requestId' => 'test-' . uniqid(),
                'timestamp' => date('c'),
                'locale' => 'en-US'
            ]
        ]);
        
        $_SERVER['HTTP_SIGNATURECERTCHAINURL'] = 'https://s3.amazonaws.com/echo.api/test';
        $_SERVER['HTTP_SIGNATURE'] = 'test-signature';
        
        $app = SkillApplication::fromGlobals(
            requestValidator: new RequestValidator(disableSignatureValidation: true)
        );
        
        $response = $app->handle();
        
        $this->assertInstanceOf(Response::class, $response);
    }
}
```

## Development Workflow

### 1. Local Development
```bash
# Install dependencies
composer install

# Run tests
composer test

# Start local server (if needed)
php -S localhost:8000 -t public/
```

### 2. Environment Variables
```bash
# .env file (not committed to git)
ALEXA_SKILL_ID=amzn1.ask.skill.my-skill
ALEXA_DEBUG=true
ALEXA_CERT_CACHE_DIR=/tmp/alexa-certs
```

### 3. Deployment Considerations
- Use `prefer-stable: true` in composer.json
- Test with signature validation enabled in production
- Configure proper certificate cache directory
- Set appropriate timeout values for your use case

## Migration from Old Structure

### From RequestHandlerRegistry to IntentRouter
```php
// Old way
$registry = new RequestHandlerRegistry([$handler1, $handler2]);

// New way
$router = new IntentRouter();
$router->onIntent('Intent1', $handler1)
       ->onIntent('Intent2', $handler2);
$registry = new IntentRouterAdapter($router);
```

### From ResponseHelper to ResponseBuilder
```php
// Old way
$helper = new ResponseHelper();
$helper->respond('Hello');
$helper->reprompt('How can I help?');
$response = $helper->getResponse();

// New way
$response = ResponseBuilder::create()
    ->text('Hello')
    ->reprompt('How can I help?')
    ->keepSession()
    ->build();
```

## Best Practices Summary

1. **Use SkillApplication** for all new skills
2. **Prefer IntentRouter** over manual handler registration
3. **Use ResponseBuilder** for all response creation
4. **Organize business logic** in Domain layer
5. **Write tests** for all handlers and business logic
6. **Use environment variables** for configuration
7. **Follow PSR standards** where applicable
8. **Keep handlers focused** on single responsibilities
9. **Use type hints** and strict types consistently
10. **Document your skill** with clear examples

This structure ensures maintainable, testable, and scalable Alexa skills using the improved framework features.
