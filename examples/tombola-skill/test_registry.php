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
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;

// Create test request
$application = new Application('amzn1.ask.skill.8e65d1a3-398e-4882-af93-66c1e306ab64');
$user = new User('test-user-123');
$session = new Session(true, 'session-123', $application, [], $user);
$launchRequest = new LaunchRequest(new DateTime(), null, 'req-123');
$request = new Request('1.0', $session, null, $launchRequest);

// Test with registry
$registry = new RequestHandlerRegistry([
    new LaunchHandler(),
]);

echo "Testing registry..." . PHP_EOL;

try {
    $handler = $registry->getSupportingHandler($request);
    echo "SUCCESS: Handler found: " . get_class($handler) . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "ERROR TYPE: " . get_class($e) . PHP_EOL;
}
