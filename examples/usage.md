# Alexa PHP SDK - Complete Usage Guide

This comprehensive guide covers all features of the Amazon Alexa PHP SDK including request handling, response building, CLI tools, and interaction model generation.

## Table of Contents

1. [Installation](#installation)
2. [Quick Start](#quick-start)
3. [Core Concepts](#core-concepts)
4. [Request Handling](#request-handling)
5. [Response Building](#response-building)
6. [SSML Generation](#ssml-generation)
7. [CLI Tools](#cli-tools)
8. [Interaction Model Generation](#interaction-model-generation)
9. [Testing](#testing)
10. [Best Practices](#best-practices)

## Installation

### From GitHub (Recommended)

```bash
# Add to your composer.json
{
    "require": {
        "rboschin/amazon-alexa-php": "dev-main"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/rboschin/amazon-alexa-php.git"
        }
    ]
}

# Install
composer install
```

### Basic Setup

```php
<?php

require_once 'vendor/autoload.php';

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Validation\RequestValidator;

// Create application
$application = new SkillApplication();

// Add your handlers
$application->addHandler(new MyIntentHandler());

// Handle request
$request = Request::fromAmazonRequest(
    file_get_contents('php://input'),
    $_SERVER['HTTP_SIGNATURECERTCHAINURL'] ?? '',
    $_SERVER['HTTP_SIGNATURE'] ?? ''
);

// Validate request (optional but recommended)
$validator = new RequestValidator();
$validator->validate($request);

// Process and return response
$response = $application->handle($request);
header('Content-Type: application/json');
echo $response;
```

## Quick Start

### 1. Create Your First Handler

```php
<?php

namespace App\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;

/**
 * Handler for Hello World intent
 * @utterances hello, say hello, hi there
 */
class HelloWorldHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        return true; // Add your skill ID validation here
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof IntentRequest &&
               $request->request->intent->name === 'HelloWorldIntent';
    }

    public function handleRequest(Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        return ResponseBuilder::create()
            ->text("Hello! Welcome to my skill.")
            ->reprompt("What would you like to do next?")
            ->keepSession()
            ->build();
    }
}
```

### 2. Set Up the Application

```php
<?php

use Rboschin\AmazonAlexa\Application\SkillApplication;
use App\Handlers\HelloWorldHandler;

$application = new SkillApplication();
$application->addHandler(new HelloWorldHandler());

// Handle the request
$response = $application->handle($request);
echo $response;
```

### 3. Generate Interaction Model

```bash
php bin/alexa generate:interaction-model \
  --skill="Hello World Skill" \
  --invocation="hello world" \
  --handlers="src/Handlers" \
  --namespace="App\\Handlers"
```

## Core Concepts

### Skill Application

The `SkillApplication` is the main entry point for your Alexa skill:

```php
use Rboschin\AmazonAlexa\Application\SkillApplication;

$application = new SkillApplication();

// Add handlers
$application->addHandler($handler1);
$application->addHandler($handler2);

// Process request
$response = $application->handle($request);
```

### Request Handlers

All handlers must extend `AbstractRequestHandler`:

```php
abstract class AbstractRequestHandler
{
    abstract public function supportsApplication(Request $request): bool;
    abstract public function supportsRequest(Request $request): bool;
    abstract public function handleRequest(Request $request): Response;
}
```

### Request Types

- **IntentRequest** - User triggers an intent
- **LaunchRequest** - User opens the skill
- **SessionEndedRequest** - User ends the session

### Response Building

Use `ResponseBuilder` for fluent response creation:

```php
ResponseBuilder::create()
    ->text("Hello world")
    ->reprompt("What next?")
    ->keepSession()
    ->build();
```

## Request Handling

### Intent Handling

```php
public function handleRequest(Request $request): Response
{
    /** @var IntentRequest $intentRequest */
    $intentRequest = $request->request;
    $intent = $intentRequest->intent;
    
    // Get intent name
    $intentName = $intent->name;
    
    // Get slot values
    $slotValue = $intent->slots['slotName']->value ?? null;
    
    // Get session attributes
    $sessionAttributes = $request->session->attributes ?? [];
    
    return ResponseBuilder::create()
        ->text("Intent {$intentName} received with slot: {$slotValue}")
        ->build();
}
```

### Launch Request Handling

```php
public function supportsRequest(Request $request): bool
{
    return $request->request instanceof LaunchRequest;
}

public function handleRequest(Request $request): Response
{
    return ResponseBuilder::create()
        ->text("Welcome to my skill!")
        ->reprompt("What would you like to do?")
        ->keepSession()
        ->build();
}
```

### Session Management

```php
use Rboschin\AmazonAlexa\Helper\SessionHelper;

// Get session attribute
$value = SessionHelper::getAttribute($request, 'key');

// Set session attribute
$response = ResponseBuilder::create()
    ->text("Response")
    ->withAttribute('key', 'value')
    ->build();

// Clear session
$response = ResponseBuilder::create()
    ->text("Goodbye!")
    ->endSession()
    ->build();
```

## Response Building

### Basic Responses

```php
use Rboschin\AmazonAlexa\Response\ResponseBuilder;

// Simple text response
$response = ResponseBuilder::create()
    ->text("Hello world!")
    ->build();

// With reprompt
$response = ResponseBuilder::create()
    ->text("Hello!")
    ->reprompt("What would you like to do?")
    ->keepSession()
    ->build();
```

### SSML Responses

```php
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Helper\SsmlGenerator;

// Using SSML directly
$response = ResponseBuilder::create()
    ->ssml("<speak>Hello <emphasis>world</emphasis>!</speak>")
    ->build();

// Using SSML Generator
$ssml = SsmlGenerator::create()
    ->text("Hello ")
    ->emphasis("world", "moderate")
    ->text("!")
    ->build();

$response = ResponseBuilder::create()
    ->ssml($ssml)
    ->build();
```

### Cards

```php
// Simple card
$response = ResponseBuilder::create()
    ->text("Here's your information")
    ->withSimpleCard("Title", "Content")
    ->build();

// Standard card with image
$response = ResponseBuilder::create()
    ->text("Here's your information")
    ->withStandardCard("Title", "Content", "https://example.com/image.jpg")
    ->build();
```

### Directives

```php
use Rboschin\AmazonAlexa\Response\Directives\Display\RenderDocumentDirective;

$response = ResponseBuilder::create()
    ->text("Display content")
    ->addDirective(new RenderDocumentDirective(
        'token',
        ['document' => $document],
        ['datasources' => $datasources]
    ))
    ->build();
```

## SSML Generation

### Basic SSML

```php
use Rboschin\AmazonAlexa\Helper\SsmlGenerator;

$ssml = SsmlGenerator::create()
    ->text("Hello ")
    ->emphasis("world", "moderate")
    ->break("1s")
    ->text("Welcome to my skill!")
    ->build();
```

### Advanced SSML

```php
$ssml = SsmlGenerator::create()
    ->sentence("Welcome to the weather skill")
    ->break("500ms")
    ->paragraph("Today's weather is")
    ->prosody("sunny", "slow", "+20%")
    ->break("1s")
    ->sayAs("25°C", "spell-out")
    ->build();
```

### Audio SSML

```php
$ssml = SsmlGenerator::create()
    ->text("Playing sound effect")
    ->audio("https://example.com/sound.mp3")
    ->text("And now some music")
    ->audio("https://example.com/music.mp3", 5000)
    ->build();
```

## CLI Tools

### Generate Handlers

```bash
# Intent handler
php bin/alexa make:intent-handler MyHandler --intent=MyIntent

# With utterances
php bin/alexa make:intent-handler OrderPizzaHandler \
  --intent=OrderPizzaIntent \
  --utterances="order pizza, get me a pizza"

# Interactive mode
php bin/alexa make:intent-handler --interactive

# Launch handler
php bin/alexa make:launch-handler LaunchHandler

# Help handler
php bin/alexa make:help-handler HelpHandler
```

### Generate Interaction Model

```bash
# Basic generation
php bin/alexa generate:interaction-model \
  --skill="My Skill" \
  --invocation="my skill"

# With custom settings
php bin/alexa generate:interaction-model \
  --skill="Pizza Skill" \
  --invocation="pizza ordering" \
  --handlers="src/Handlers" \
  --namespace="App\\Handlers" \
  --locale="en-US" \
  --output="models/interaction-model.json"
```

### List Templates

```bash
php bin/alexa list
```

## Interaction Model Generation

### Using @utterances in DocBlocks

```php
/**
 * Handler for ordering pizza
 * @utterances order pizza, get me a pizza
 * @utterances I want a {size} {pizzaType} pizza
 * @utterances can I get a pizza with {topping}
 */
class OrderPizzaHandler extends AbstractRequestHandler
{
    // Implementation...
}
```

### Automatic Features

The generator automatically:
- **Extracts intents** from handler methods
- **Parses utterances** from `@utterances` tags
- **Detects slots** from request handling code
- **Infers slot types** (AMAZON.NUMBER, AMAZON.DATE, etc.)
- **Generates sample utterances** if none provided
- **Creates valid JSON** for Alexa Skills Kit

### Generated Model Structure

```json
{
  "interactionModel": {
    "languageModel": {
      "invocationName": "pizza ordering",
      "intents": [
        {
          "name": "OrderPizzaIntent",
          "samples": [
            "order pizza",
            "get me a pizza",
            "I want a {size} {pizzaType} pizza"
          ],
          "slots": [
            {
              "name": "size",
              "type": "AMAZON.LITERAL"
            },
            {
              "name": "pizzaType", 
              "type": "AMAZON.LITERAL"
            }
          ]
        }
      ]
    }
  }
}
```

## Testing

### Unit Testing Handlers

```php
<?php

use PHPUnit\Framework\TestCase;
use Rboschin\AmazonAlexa\TestSupport\IntentRequestFactory;
use App\Handlers\MyHandler;

class MyHandlerTest extends TestCase
{
    public function testHandlesIntent(): void
    {
        $handler = new MyHandler();
        $request = IntentRequestFactory::create('MyIntent');
        
        $this->assertTrue($handler->supportsRequest($request));
        
        $response = $handler->handleRequest($request);
        $this->assertStringContains('MyIntent received', $response->response->outputSpeech->text);
    }
    
    public function testHandlesIntentWithSlots(): void
    {
        $handler = new MyHandler();
        $request = IntentRequestFactory::create('MyIntent', [
            'itemName' => 'pizza'
        ]);
        
        $response = $handler->handleRequest($request);
        $this->assertStringContains('pizza', $response->response->outputSpeech->text);
    }
}
```

### Testing with Session Attributes

```php
public function testSessionAttributes(): void
{
    $handler = new MyHandler();
    $request = IntentRequestFactory::create('MyIntent', [], [
        'counter' => 1
    ]);
    
    $response = $handler->handleRequest($request);
    
    // Check session attributes are preserved/modified
    $this->assertEquals(2, $response->sessionAttributes['counter'] ?? null);
}
```

### Testing Launch and Help Requests

```php
use Rboschin\AmazonAlexa\TestSupport\IntentRequestFactory;

public function testLaunchRequest(): void
{
    $request = IntentRequestFactory::createLaunch();
    $response = $this->launchHandler->handleRequest($request);
    
    $this->assertStringContains('Welcome', $response->response->outputSpeech->text);
}

public function testHelpRequest(): void
{
    $request = IntentRequestFactory::create('AMAZON.HelpIntent');
    $response = $this->helpHandler->handleRequest($request);
    
    $this->assertStringContains('help', $response->response->outputSpeech->text);
}
```

## Best Practices

### 1. Handler Organization

```php
src/
├── Handlers/
│   ├── Intent/
│   │   ├── OrderPizzaHandler.php
│   │   └── CancelOrderHandler.php
│   ├── LaunchHandler.php
│   └── HelpHandler.php
└── Services/
    ├── PizzaService.php
    └── OrderService.php
```

### 2. Response Consistency

```php
// Good: Use ResponseBuilder for all responses
return ResponseBuilder::create()
    ->text($message)
    ->reprompt($reprompt)
    ->keepSession()
    ->build();

// Avoid: Direct Response construction
```

### 3. Error Handling

```php
public function handleRequest(Request $request): Response
{
    try {
        $result = $this->processOrder($request);
        return ResponseBuilder::create()
            ->text("Order placed successfully")
            ->build();
    } catch (ValidationException $e) {
        return ResponseBuilder::create()
            ->text("Sorry, there was an error: {$e->getMessage()}")
            ->reprompt("Please try again")
            ->keepSession()
            ->build();
    }
}
```

### 4. Session Management

```php
// Use SessionHelper for consistency
use Rboschin\AmazonAlexa\Helper\SessionHelper;

// Get attributes
$userId = SessionHelper::getAttribute($request, 'userId');
$step = SessionHelper::getAttribute($request, 'currentStep');

// Set attributes in response
return ResponseBuilder::create()
    ->text("Response")
    ->withAttribute('currentStep', 'nextStep')
    ->withAttribute('timestamp', time())
    ->build();
```

### 5. Validation

```php
public function supportsApplication(Request $request): bool
{
    // Always validate skill ID in production
    $skillId = $request->context->system->application->applicationId ?? null;
    return $skillId === 'amzn1.ask.skill.your-skill-id';
}
```

### 6. Testing Strategy

- **Unit tests** for individual handlers
- **Integration tests** for complete flows
- **Interaction model tests** for utterance coverage
- **End-to-end tests** with actual Alexa service

### 7. Documentation

```php
/**
 * Handler for ordering pizza with custom toppings
 * 
 * Supports the following utterances:
 * - "order a {size} {pizzaType} pizza"
 * - "get me a pizza with {topping}"
 * - "I want a {pizzaType} pizza"
 * 
 * @var PizzaService $pizzaService
 */
class OrderPizzaHandler extends AbstractRequestHandler
{
    // Implementation...
}
```

## Migration from Previous Versions

### Namespace Changes

```php
// Old (MaxBeckers)
use MaxBeckers\AmazonAlexa\RequestHandler\AbstractRequestHandler;

// New (Rboschin)  
use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
```

### API Changes

```php
// Old way
$helper = new ResponseHelper();
$response = $helper->response($text);

// New way (recommended)
$response = ResponseBuilder::create()->text($text)->build();

// Backward compatible (still works)
$helper = new ResponseHelper();
$response = $helper->response($text);
```

## Complete Example

### Pizza Ordering Skill

```php
<?php

// index.php - Entry point
require_once 'vendor/autoload.php';

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\Validation\RequestValidator;
use Rboschin\AmazonAlexa\Request\Request;
use App\Handlers\{
    LaunchHandler,
    OrderPizzaHandler,
    HelpHandler,
    CancelOrderHandler
};

// Create application
$application = new SkillApplication();

// Add handlers
$application->addHandler(new LaunchHandler());
$application->addHandler(new OrderPizzaHandler());
$application->addHandler(new HelpHandler());
$application->addHandler(new CancelOrderHandler());

// Create request
$request = Request::fromAmazonRequest(
    file_get_contents('php://input'),
    $_SERVER['HTTP_SIGNATURECERTCHAINURL'] ?? '',
    $_SERVER['HTTP_SIGNATURE'] ?? ''
);

// Validate and handle
$validator = new RequestValidator();
$validator->validate($request);

$response = $application->handle($request);

// Return response
header('Content-Type: application/json');
echo $response;
```

```php
<?php

// src/Handlers/OrderPizzaHandler.php
namespace App\Handlers;

use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use App\Services\PizzaService;

/**
 * Handler for ordering pizza
 * @utterances order pizza, get me a pizza
 * @utterances I want a {size} {pizzaType} pizza
 * @utterances can I get a pizza with {topping}
 */
class OrderPizzaHandler extends AbstractRequestHandler
{
    private PizzaService $pizzaService;
    
    public function __construct(PizzaService $pizzaService = null)
    {
        $this->pizzaService = $pizzaService ?? new PizzaService();
    }
    
    public function supportsApplication(Request $request): bool
    {
        return true; // Add skill ID validation
    }
    
    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest &&
               $request->request->intent->name === 'OrderPizzaIntent';
    }
    
    public function handleRequest(Request $request): \Rboschin\AmazonAlexa\Response\Response
    {
        $intent = $request->request->intent;
        $size = $intent->slots['size']->value ?? 'medium';
        $pizzaType = $intent->slots['pizzaType']->value ?? 'margherita';
        $topping = $intent->slots['topping']->value ?? null;
        
        try {
            $order = $this->pizzaService->createOrder($size, $pizzaType, $topping);
            
            return ResponseBuilder::create()
                ->text("I've ordered a {$size} {$pizzaType} pizza for you. It will be ready in 20 minutes.")
                ->withAttribute('lastOrder', $order->getId())
                ->build();
                
        } catch (\Exception $e) {
            return ResponseBuilder::create()
                ->text("Sorry, I couldn't place your order. Please try again.")
                ->reprompt("What kind of pizza would you like?")
                ->keepSession()
                ->build();
        }
    }
}
```

This comprehensive guide provides everything needed to build production-ready Alexa skills using the Amazon Alexa PHP SDK.
