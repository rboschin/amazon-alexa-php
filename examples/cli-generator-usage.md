# CLI Generator Usage Examples

This document shows how to use the Alexa CLI generator tool.

## Basic Usage

### Generate an Intent Handler

```bash
# Basic usage
php bin/alexa make:intent-handler MyIntentHandler --intent=MyIntent

# Output:
# ✓ Created intent handler: MyIntentHandler.php
```

**Generated file:**
```php
<?php

declare(strict_types=1);

namespace App\Handlers;

use MaxBeckers\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\Response\ResponseBuilder;

/**
 * Handler for MyIntent intent
 */
class MyIntentHandler extends AbstractRequestHandler
{
    public function supportsApplication(Request $request): bool
    {
        return true; // Add your skill ID check here
    }

    public function supportsRequest(Request $request): bool
    {
        return $request->request instanceof \MaxBeckers\AmazonAlexa\Request\Request\Standard\IntentRequest &&
               $request->request->intent->name === 'MyIntent';
    }

    public function handleRequest(Request $request): \MaxBeckers\AmazonAlexa\Response\Response
    {
        $slotValue = $request->request->intent->slots['exampleSlot']->value ?? null;
        
        return ResponseBuilder::create()
            ->text("You triggered MyIntent with slot value: {$slotValue}")
            ->reprompt("What else would you like to do?")
            ->keepSession()
            ->build();
    }
}
```

### Generate a Launch Handler

```bash
php bin/alexa make:launch-handler LaunchHandler

# Output:
# ✓ Created launch handler: LaunchHandler.php
```

### Generate a Help Handler

```bash
php bin/alexa make:help-handler HelpHandler

# Output:
# ✓ Created help handler: HelpHandler.php
```

## Advanced Usage

### Multiple Handlers

```bash
# Generate multiple handlers for a complete skill
php bin/alexa make:intent-handler OrderPizzaHandler --intent=OrderPizzaIntent
php bin/alexa make:intent-handler TrackOrderHandler --intent=TrackOrderIntent
php bin/alexa make:launch-handler LaunchHandler
php bin/alexa make:help-handler HelpHandler

# Directory structure after generation:
src/Handlers/
├── LaunchHandler.php
├── HelpHandler.php
├── OrderPizzaHandler.php
└── TrackOrderHandler.php
```

### Integration with SkillApplication

After generating handlers, you can integrate them into your skill:

```php
<?php
require 'vendor/autoload.php';

use MaxBeckers\AmazonAlexa\Application\SkillApplication;
use MaxBeckers\AmazonAlexa\RequestHandler\IntentRouter;
use MaxBeckers\AmazonAlexa\RequestHandler\IntentRouterAdapter;
use App\Handlers\LaunchHandler;
use App\Handlers\HelpHandler;
use App\Handlers\OrderPizzaHandler;
use App\Handlers\TrackOrderHandler;

// Create router and register generated handlers
$router = new IntentRouter();
$router->onLaunch(new LaunchHandler())
       ->onIntent('AMAZON.HelpIntent', new HelpHandler())
       ->onIntent('OrderPizzaIntent', new OrderPizzaHandler())
       ->onIntent('TrackOrderIntent', new TrackOrderHandler())
       ->onFallback(new HelpHandler()); // Fallback to help

// Create and run application
$app = SkillApplication::fromGlobals(
    requestHandlerRegistry: new IntentRouterAdapter($router)
);

$response = $app->handle();
echo json_encode($response);
```

## Available Templates

List all available templates:

```bash
php bin/alexa list

# Output:
# ℹ Available templates:
#   intent-handler.php.template - Generate intent handler
#   launch-handler.php.template - Generate launch handler
#   help-handler.php.template - Generate help handler
```

## Customization

### Modifying Templates

You can modify the templates in `templates/` directory to match your project's:

- Namespace structure
- Coding standards
- Default responses
- Error handling patterns
- Documentation style

### Adding New Templates

1. Create a new template file in `templates/` directory
2. Add a case in the CLI switch statement
3. Update the `listTemplates()` method
4. Document the new template in this file

## Best Practices

1. **Use meaningful names**: Choose descriptive class and intent names
2. **Follow naming conventions**: Use PascalCase for class names
3. **Add proper documentation**: Include PHPDoc for generated classes
4. **Implement all methods**: Ensure `supportsApplication`, `supportsRequest`, and `handleRequest` are properly implemented
5. **Test your handlers**: Write unit tests for generated handlers
6. **Use ResponseBuilder**: Leverage the fluent API for responses
7. **Handle edge cases**: Consider null values, missing slots, and error conditions

## Integration with Existing Projects

If you have an existing project structure:

1. **Adjust namespace**: Modify the templates to match your namespace
2. **Update output path**: Change the default `src/Handlers` directory
3. **Customize templates**: Add your project's patterns and conventions

## Troubleshooting

### Permission Denied

```bash
chmod +x bin/alexa
```

### File Already Exists

The CLI will warn you if a file already exists and won't overwrite it.

### Template Not Found

Ensure the `templates/` directory exists and contains the template files.

This CLI tool significantly reduces boilerplate when creating new Alexa skill handlers and helps maintain consistent project structure.
