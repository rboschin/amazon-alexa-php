<?php

declare(strict_types=1);

use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Response\Response;

/**
 * Just a simple example request handler for a card response.
 * To create a response with an image @see https://developer.amazon.com/de/docs/custom-skills/include-a-card-in-your-skills-response.html#creating-a-home-card-to-display-text-and-an-image
 */
class CardResponseRequestHandler extends AbstractRequestHandler
{
    public function __construct(
        private readonly ResponseHelper $responseHelper
    ) {
        parent::__construct();
        $this->supportedApplicationIds = ['my_amazon_skill_id'];
    }

    public function supportsRequest(Request $request): bool
    {
        // support all intent requests, should not be done.
        return $request->request instanceof IntentRequest;
    }

    public function handleRequest(Request $request): Response
    {
        $card = Card::createSimple(
            'Example of the Card Title',
            "Example of card content. This card has just plain text content.\nThe content is formatted with line breaks to improve readability."
        );
        $this->responseHelper->card($card);

        return $this->responseHelper->respond('Text to speak back to the user.');
    }
}
