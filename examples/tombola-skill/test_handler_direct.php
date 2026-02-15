<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use TombolaNapoletana\Config\TombolaConfig;
use TombolaNapoletana\Handlers\LaunchHandler;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\LaunchRequest;
use Rboschin\AmazonAlexa\Request\Application;
use Rboschin\AmazonAlexa\Request\User;
use Rboschin\AmazonAlexa\Request\Session;

// Create test request
$application = new Application('amzn1.ask.skill.8e65d1a3-398e-4882-af93-66c1e306ab64');
$user = new User('test-user-123');
$session = new Session(true, 'session-123', $application, [], $user);
$launchRequest = new LaunchRequest(new DateTime(), null, 'req-123');
$request = new Request('1.0', $session, null, $launchRequest);

// Test handler directly
$handler = new LaunchHandler();

echo "Testing handler directly..." . PHP_EOL;

try {
    $response = $handler->handleRequest($request);
    echo "SUCCESS: Response generated" . PHP_EOL;
    echo "Response type: " . get_class($response) . PHP_EOL;
    echo "Response content: " . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "ERROR TYPE: " . get_class($e) . PHP_EOL;
    echo "TRACE: " . $e->getTraceAsString() . PHP_EOL;
}
