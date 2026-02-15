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

// Test handler
$handler = new LaunchHandler();

echo "Testing supportsApplication: " . ($handler->supportsApplication($request) ? 'PASS' : 'FAIL') . PHP_EOL;
echo "Testing supportsRequest: " . ($handler->supportsRequest($request) ? 'PASS' : 'FAIL') . PHP_EOL;

if ($handler->supportsApplication($request) && $handler->supportsRequest($request)) {
    echo "Attempting to handle request..." . PHP_EOL;
    try {
        $response = $handler->handleRequest($request);
        echo "SUCCESS: Response generated" . PHP_EOL;
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . PHP_EOL;
        echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
        echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
    }
}
