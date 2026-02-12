[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)
[![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](https://github.com/rboschin/amazon-alexa-php/issues)

# Amazon Alexa PHP Library

A modern PHP library for building Amazon Alexa skills with clean, maintainable code and fluent APIs. This library significantly reduces boilerplate code and provides an intuitive development workflow for Alexa skill developers.

## 🚀 Features

### Core Improvements
- **🔧 SkillApplication**: Simplified bootstrap (reduces ~50 lines to ~15 lines)
- **🎯 IntentRouter**: Expressive intent routing with fluent API
- **📝 ResponseBuilder**: Chainable response creation with modern syntax
- **🔒 RequestValidator**: Configurable validation with PSR-18 support
- **🎤 SsmlGenerator**: Fluent SSML generation with helper methods
- **💾 SessionHelper**: Convenient session attribute management

### Development Tools
- **🧪 IntentRequestFactory**: Easy test request creation
- **⚡ CLI Generator**: Command-line tool for generating handler skeletons
- **📚 Complete Documentation**: Comprehensive guides and examples

### Legacy Features
- **✅ Request Validation**: Automatic verification of Amazon request signatures
- **🔧 Flexible Handler System**: Easy-to-extend request handler architecture
- **🎤 SSML Support**: Built-in Speech Synthesis Markup Language generator
- **🖥 APL Support**: Create and send Alexa Presentation Language documents
- **📍 Device Address API**: Helper for accessing user location data
- **🚀 PHP 8.2+ Ready**: Leverages modern PHP features and type safety

## 📋 Requirements

- PHP 8.2 or higher
- ext-openssl (for request validation)

## 🔧 Installation

### From GitHub (Recommended)

```bash
# Clone the repository
git clone https://github.com/rboschin/amazon-alexa-php.git

# Navigate to the directory
cd amazon-alexa-php

# Install dependencies
composer install
```

### In Your Project

```bash
# Add to your project's composer.json
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
composer update rboschin/amazon-alexa-php
```

## 🚀 Quick Start

### Modern Approach (Recommended)

```php
'''php
<?php
require 'vendor/autoload.php';

use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouter;
use Rboschin\AmazonAlexa\RequestHandler\IntentRouterAdapter;

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

### Legacy Approach (Still Supported)


```php
<?php
require 'vendor/autoload.php';

use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Validation\RequestValidator;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;

// 1. Parse incoming request
$requestBody = file_get_contents('php://input');
$alexaRequest = Request::fromAmazonRequest(
    $requestBody, 
    $_SERVER['HTTP_SIGNATURECERTCHAINURL'] ?? '', 
    $_SERVER['HTTP_SIGNATURE'] ?? ''
);

// 2. Validate request
$validator = new RequestValidator();
$validator->validate($alexaRequest);

// 3. Handle request
$registry = new RequestHandlerRegistry([$handler1, $handler2]);
$handler = $registry->getSupportingHandler($alexaRequest);
$response = $handler->handleRequest($alexaRequest);

// 4. Send response
echo json_encode($response);
'''

## 📚 Documentation

### Getting Started Guides
- [📖 Project Structure](docs/PROJECT_STRUCTURE.md) - Recommended organization and best practices
- [🎯 Objectives](docs/OBJECTIVES.md) - Development goals and requirements
- [📋 Work Plan](docs/WORKPLAN.md) - Detailed implementation phases
- [🔧 Improvements](docs/IMPROVEMENTS.md) - Complete list of enhancements

### Examples
- [🏗 Basic Skill Application](examples/skill-application-basic.php) - Modern bootstrap example
- [🎯 Intent Router Usage](examples/intent-router-basic.php) - Expressive routing patterns
- [📝 Response Builder Fluent](examples/response-builder-fluent.php) - Chainable response creation
- [🔒 Improved Request Validator](examples/request-validator-improved.php) - Advanced validation features
- [🎤 SSML Generator Fluent](examples/ssml-generator-fluent.php) - Modern SSML generation
- [🏆 Complete Modern Skill](examples/complete-modern-skill.php) - Full-featured example
- [⚡ CLI Generator Usage](examples/cli-generator-usage.md) - Command-line tool guide

### API Reference
- [📖 API Documentation](https://github.com/rboschin/amazon-alexa-php/docs) - Complete API reference
- [🧪 Testing Guide](docs/PROJECT_STRUCTURE.md#testing-structure) - Testing best practices

## 🛠️ Development Tools

### CLI Generator

Generate handler skeletons quickly:

'''bash
# Generate intent handler
php bin/alexa make:intent-handler MyIntentHandler --intent=MyIntent

# Generate launch handler  
php bin/alexa make:launch-handler LaunchHandler

# Generate help handler
php bin/alexa make:help-handler HelpHandler
'''

### Testing Utilities

Create test requests easily:

'''php
use Rboschin\AmazonAlexa\TestSupport\IntentRequestFactory;

// Create intent request
$request = IntentRequestFactory::forIntent('MyIntent', ['slot' => 'value']);

// Create launch request
$launchRequest = IntentRequestFactory::forLaunch();

// Create request with session attributes
$request = IntentRequestFactory::withSessionAttributes($request, ['key' => 'value']);
'''

## 🧪 Testing

'''bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit test/Test/Application/SkillApplicationTest.php

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage
'''

## 📦 Optional Dependencies

Enhance your installation with these optional packages:

'''bash
# PSR-18 HTTP client support
composer require psr/http-client

# PSR-7 message factory support  
composer require psr/http-factory

# PSR-7 implementation
composer require guzzlehttp/psr7
# or
composer require nyholm/psr7
'''

## 🔧 Configuration

### Environment Variables

'''bash
# .env file
ALEXA_SKILL_ID=amzn1.ask.skill.your-skill
ALEXA_DEBUG=false
ALEXA_CERT_CACHE_DIR=/tmp/alexa-certs
'''

### Request Validator Configuration

'''php
use Rboschin\AmazonAlexa\Validation\RequestValidator;

// Development mode (disable signature validation)
$validator = new RequestValidator(disableSignatureValidation: true);

// Custom certificate cache directory
$validator = new RequestValidator(certCacheDir: '/custom/cache/path');

// Custom timestamp tolerance (seconds)
$validator = new RequestValidator(timestampTolerance: 300);

// PSR-18 HTTP client
$psr18Client = new MyPsr18Client();
$validator = new RequestValidator(client: $psr18Client);
'''

## 🔄 Migration from Original Package

If you're migrating from the original `maxbeckers/amazon-alexa-php` package:

### 1. Update Your Dependencies

```bash
# Remove original package (if installed)
composer remove maxbeckers/amazon-alexa-php

# Add GitHub repository to your composer.json
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

# Install the new package
composer update rboschin/amazon-alexa-php
```

### 2. Update Namespace

```php
// Old
use MaxBeckers\AmazonAlexa\Request\Request;

// New  
use Rboschin\AmazonAlexa\Request\Request;
'''

### 3. Modernize Your Code

'''php
// Old approach
$registry = new RequestHandlerRegistry([$handler1, $handler2]);

// New approach
$router = new IntentRouter();
$router->onIntent('MyIntent', $handler1)
       ->onIntent('AnotherIntent', $handler2);
$registry = new IntentRouterAdapter($router);
'''

## 🏗️ Architecture

### Modern Development Workflow

1. **Bootstrap** - Use 'SkillApplication::fromGlobals()' for simplified setup
2. **Routing** - Use 'IntentRouter' for expressive handler registration  
3. **Responses** - Use 'ResponseBuilder' for chainable response creation
4. **SSML** - Use 'SsmlGenerator::create()' for fluent speech generation
5. **Testing** - Use 'IntentRequestFactory' for easy test creation
6. **Development** - Use CLI generator for rapid prototyping

### Backward Compatibility

All legacy APIs remain fully functional. You can gradually migrate to modern approaches at your own pace.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch ('git checkout -b feature/amazing-feature')
3. Commit your changes ('git commit -am 'Add amazing feature'')
4. Push to the branch ('git push origin feature/amazing-feature')
5. Open a Pull Request

### Development Setup

'''bash
# Clone the repository
git clone https://github.com/rboschin/amazon-alexa-php.git
cd amazon-alexa-php

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs
'''

## 📄 License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Original library by [Maximilian Beckers](https://github.com/maxbeckers)
- Enhanced and maintained by [Roberto Boschin](https://github.com/rboschin)
- Community contributions and feedback

## 🔗 Links

- [GitHub Repository](https://github.com/rboschin/amazon-alexa-php)
- [Issue Tracker](https://github.com/rboschin/amazon-alexa-php/issues)
- [Documentation](https://github.com/rboschin/amazon-alexa-php/tree/master/docs)

---

**⚡ Ready to build amazing Alexa skills with modern PHP development!**
