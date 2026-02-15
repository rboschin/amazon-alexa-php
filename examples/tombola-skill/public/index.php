<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use TombolaNapoletana\Config\TombolaConfig;
use TombolaNapoletana\Handlers\LaunchHandler;
use TombolaNapoletana\Handlers\ExtractNumberHandler;
use TombolaNapoletana\Handlers\RepeatNumberHandler;
use TombolaNapoletana\Handlers\CheckNumberHandler;
use TombolaNapoletana\Handlers\CheckWinningHandler;
use TombolaNapoletana\Handlers\ModeHandler;
use TombolaNapoletana\Handlers\GameControlHandler;
use TombolaNapoletana\Handlers\StatusHandler;
use TombolaNapoletana\Handlers\ProvideNumbersHandler;
use TombolaNapoletana\Handlers\YesNoHandler;
use TombolaNapoletana\Handlers\HelpHandler;
use TombolaNapoletana\Handlers\FallbackHandler;
use Rboschin\AmazonAlexa\Application\SkillApplication;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use Rboschin\AmazonAlexa\Validation\RequestValidator;

// Load environment
require_once __DIR__ . '/../src/Config/TombolaConfig.php';

// Enable error reporting for development
if (TombolaConfig::isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Create request handler registry with all handlers
$registry = new RequestHandlerRegistry([
    new LaunchHandler(),
    new ExtractNumberHandler(),
    new RepeatNumberHandler(),
    new CheckNumberHandler(),
    new CheckWinningHandler(),
    new ModeHandler(),
    new GameControlHandler(),
    new StatusHandler(),
    new ProvideNumbersHandler(),
    new YesNoHandler(),
    new HelpHandler(),
    new FallbackHandler(),
]);

try {
    // Create skill application with validation disabled for development
    // NOTE: In production, you should enable signature validation
    $app = SkillApplication::fromGlobals(
        new RequestValidator(
            timestampTolerance: 150,
            client: null,
            certCacheDir: null,
            disableSignatureValidation: TombolaConfig::isDebug() // Disable validation in debug mode
        ),
        $registry
    );
    
    $response = $app->handle();
    
    // Set response headers
    header('Content-Type: application/json;charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    // Output JSON response
    echo json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    
} catch (\Throwable $e) {
    // Log error
    error_log('Tombola Skill Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    header('Content-Type: application/json;charset=UTF-8');
    
    echo json_encode([
        'error' => 'Internal server error',
        'message' => TombolaConfig::isDebug() ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() : 'Something went wrong'
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
