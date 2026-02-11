<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\RequestHandler\Basic;

use Rboschin\AmazonAlexa\Helper\ResponseHelper;
use Rboschin\AmazonAlexa\Request\Request;
use Rboschin\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Rboschin\AmazonAlexa\RequestHandler\AbstractRequestHandler;
use Rboschin\AmazonAlexa\Response\Response;

class NavigateHomeRequestHandler extends AbstractRequestHandler
{
    public function __construct(
        private readonly ResponseHelper $responseHelper,
        private readonly string $output,
        array $supportedApplicationIds
    ) {
        parent::__construct();
        $this->supportedApplicationIds = $supportedApplicationIds;
    }

    public function supportsRequest(Request $request): bool
    {
        // support amazon navigate home request, amazon default intents are prefixed with "AMAZON."
        return $request->request instanceof IntentRequest && 'AMAZON.NavigateHomeIntent' === $request->request->intent->name;
    }

    public function handleRequest(Request $request): Response
    {
        return $this->responseHelper->respond($this->output);
    }
}
