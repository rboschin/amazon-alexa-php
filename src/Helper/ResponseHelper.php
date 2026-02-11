<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Helper;

use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Response\Directives\Directive;
use Rboschin\AmazonAlexa\Response\OutputSpeech;
use Rboschin\AmazonAlexa\Response\Reprompt;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Response\ResponseBody;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;

/**
 * This helper class can create simple responses for the most needed intents.
 * 
 * Note: This class now uses ResponseBuilder internally for better consistency,
 * but maintains the same public API for backward compatibility.
 */
class ResponseHelper
{
    /**
     * @param Response|null $response The response object
     * @param ResponseBody|null $responseBody The response body object
     */
    public function __construct(
        public ?Response $response = null,
        public ?ResponseBody $responseBody = null,
    ) {
        if ($this->response === null || $this->responseBody === null) {
            $this->resetResponse();
        }
    }

    /**
     * Add a plaintext respond to response.
     */
    public function respond(string $text, bool $endSession = false): ?Response
    {
        $builder = ResponseBuilder::create()
            ->text($text)
            ->endSession($endSession);
            
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a ssml respond to response.
     */
    public function respondSsml(string $ssml, bool $endSession = false): ?Response
    {
        $builder = ResponseBuilder::create()
            ->ssml($ssml)
            ->endSession($endSession);
            
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a plaintext reprompt to response.
     */
    public function reprompt(string $text): ?Response
    {
        $builder = ResponseBuilder::create();
        
        // Preserve existing speech
        if ($this->responseBody->outputSpeech) {
            if ($this->responseBody->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->text($this->responseBody->outputSpeech->text);
            } else {
                $builder->ssml($this->responseBody->outputSpeech->ssml);
            }
        }
        
        // Preserve existing card
        if ($this->responseBody->card) {
            $builder->card($this->responseBody->card);
        }
        
        // Preserve existing directives
        foreach ($this->responseBody->directives as $directive) {
            $builder->directive($directive);
        }
        
        // Preserve session end setting
        $builder->endSession($this->responseBody->shouldEndSession ?? false);
        
        // Add reprompt
        $builder->reprompt($text);
        
        // Preserve session attributes
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a ssml reprompt to response.
     */
    public function repromptSsml(string $ssml): ?Response
    {
        $builder = ResponseBuilder::create();
        
        // Preserve existing speech
        if ($this->responseBody->outputSpeech) {
            if ($this->responseBody->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->text($this->responseBody->outputSpeech->text);
            } else {
                $builder->ssml($this->responseBody->outputSpeech->ssml);
            }
        }
        
        // Preserve existing card
        if ($this->responseBody->card) {
            $builder->card($this->responseBody->card);
        }
        
        // Preserve existing directives
        foreach ($this->responseBody->directives as $directive) {
            $builder->directive($directive);
        }
        
        // Preserve session end setting
        $builder->endSession($this->responseBody->shouldEndSession ?? false);
        
        // Add SSML reprompt
        $builder->repromptSsml($ssml);
        
        // Preserve session attributes
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a card to response.
     */
    public function card(Card $card): ?Response
    {
        $builder = ResponseBuilder::create();
        
        // Preserve existing speech
        if ($this->responseBody->outputSpeech) {
            if ($this->responseBody->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->text($this->responseBody->outputSpeech->text);
            } else {
                $builder->ssml($this->responseBody->outputSpeech->ssml);
            }
        }
        
        // Preserve existing reprompt
        if ($this->responseBody->reprompt) {
            if ($this->responseBody->reprompt->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->reprompt($this->responseBody->reprompt->outputSpeech->text);
            } else {
                $builder->repromptSsml($this->responseBody->reprompt->outputSpeech->ssml);
            }
        }
        
        // Preserve existing directives
        foreach ($this->responseBody->directives as $directive) {
            $builder->directive($directive);
        }
        
        // Preserve session end setting
        $builder->endSession($this->responseBody->shouldEndSession ?? false);
        
        // Add card
        $builder->card($card);
        
        // Preserve session attributes
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a directive to response.
     */
    public function directive(Directive $directive): ?Response
    {
        $builder = ResponseBuilder::create();
        
        // Preserve existing speech
        if ($this->responseBody->outputSpeech) {
            if ($this->responseBody->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->text($this->responseBody->outputSpeech->text);
            } else {
                $builder->ssml($this->responseBody->outputSpeech->ssml);
            }
        }
        
        // Preserve existing reprompt
        if ($this->responseBody->reprompt) {
            if ($this->responseBody->reprompt->outputSpeech->type === OutputSpeech::TYPE_PLAINTEXT) {
                $builder->reprompt($this->responseBody->reprompt->outputSpeech->text);
            } else {
                $builder->repromptSsml($this->responseBody->reprompt->outputSpeech->ssml);
            }
        }
        
        // Preserve existing card
        if ($this->responseBody->card) {
            $builder->card($this->responseBody->card);
        }
        
        // Preserve existing directives
        foreach ($this->responseBody->directives as $existingDirective) {
            $builder->directive($existingDirective);
        }
        
        // Preserve session end setting
        $builder->endSession($this->responseBody->shouldEndSession ?? false);
        
        // Add new directive
        $builder->directive($directive);
        
        // Preserve session attributes
        if (!empty($this->response->sessionAttributes)) {
            $builder->sessionAttributes($this->response->sessionAttributes);
        }
        
        $this->response = $builder->build();
        $this->responseBody = $this->response->response;

        return $this->response;
    }

    /**
     * Add a new attribute to response session attributes.
     */
    public function addSessionAttribute(string $key, string $value): void
    {
        $this->response->sessionAttributes[$key] = $value;
    }

    /**
     * Reset the response in ResponseHelper.
     */
    public function resetResponse(): void
    {
        $this->responseBody = new ResponseBody();
        $this->response = new Response([], '1.0', $this->responseBody);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
