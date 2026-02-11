<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\RequestHandler\Basic\HelpRequestHandler;
use Rboschin\AmazonAlexa\RequestHandler\RequestHandlerRegistry;
use Rboschin\AmazonAlexa\Validation\RequestValidator;

require '../vendor/autoload.php';
require 'Handlers/SimpleIntentRequestHandler.php';

/**
 * Simple example for request handling workflow with help example
 * loading json
 * creating request
 * validating request
 * adding request handler to registry
 * handling request
 * returning json response
 */
$requestBody = file_get_contents('php://input');
if ($requestBody) {
    $alexaRequest = Request::fromAmazonRequest($requestBody, $_SERVER['HTTP_SIGNATURECERTCHAINURL'], $_SERVER['HTTP_SIGNATURE']);

    if (!$alexaRequest) {
        http_response_code(400);
        exit();
    }

    // Request validation
    $validator = new RequestValidator();
    $validator->validate($alexaRequest);

    // add handlers to registry
    $responseHelper = new ResponseHelper();
    $helpRequestHandler = new HelpRequestHandler($responseHelper, 'Help Text', ['my_amazon_skill_id']);
    $mySimpleRequestHandler = new SimpleIntentRequestHandler($responseHelper);
    $requestHandlerRegistry = new RequestHandlerRegistry([$helpRequestHandler, $mySimpleRequestHandler]);

    // handle request
    $requestHandler = $requestHandlerRegistry->getSupportingHandler($alexaRequest);
    $response = $requestHandler->handleRequest($alexaRequest);

    // render response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(400);
}
exit();
