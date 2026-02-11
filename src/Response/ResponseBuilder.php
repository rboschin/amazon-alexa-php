<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

/**
 * ResponseBuilder provides a fluent API for creating Alexa responses.
 * 
 * This class offers a more expressive and chainable way to build responses
 * compared to the step-by-step ResponseHelper approach.
 * 
 * Usage:
 * ```php
 * $response = ResponseBuilder::create()
 *     ->text('Welcome to my skill!')
 *     ->reprompt('What would you like to do?')
 *     ->card($card)
 *     ->keepSession()
 *     ->build();
 * ```
 */
class ResponseBuilder
{
    private Response $response;
    private ResponseBody $responseBody;

    private function __construct()
    {
        $this->response = new Response();
        $this->responseBody = $this->response->response;
    }

    /**
     * Create a new ResponseBuilder instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set plain text speech
     */
    public function text(string $text): self
    {
        $this->responseBody->outputSpeech = OutputSpeech::createByText($text);
        return $this;
    }

    /**
     * Set SSML speech
     */
    public function ssml(string $ssml): self
    {
        $this->responseBody->outputSpeech = OutputSpeech::createBySsml($ssml);
        return $this;
    }

    /**
     * Set plain text reprompt
     */
    public function reprompt(string $text): self
    {
        $this->responseBody->reprompt = new Reprompt(
            outputSpeech: OutputSpeech::createByText($text)
        );
        return $this;
    }

    /**
     * Set SSML reprompt
     */
    public function repromptSsml(string $ssml): self
    {
        $this->responseBody->reprompt = new Reprompt(
            outputSpeech: OutputSpeech::createBySsml($ssml)
        );
        return $this;
    }

    /**
     * Add a card to the response
     */
    public function card(Card $card): self
    {
        $this->responseBody->card = $card;
        return $this;
    }

    /**
     * Add a directive to the response
     */
    public function directive(Directive $directive): self
    {
        $this->responseBody->addDirective($directive);
        return $this;
    }

    /**
     * Set whether the session should end
     */
    public function endSession(bool $end = true): self
    {
        $this->responseBody->shouldEndSession = $end;
        return $this;
    }

    /**
     * Keep the session open (shortcut for endSession(false))
     */
    public function keepSession(): self
    {
        return $this->endSession(false);
    }

    /**
     * Add session attributes
     */
    public function sessionAttributes(array $attributes): self
    {
        $this->response->sessionAttributes = $attributes;
        return $this;
    }

    /**
     * Add a single session attribute
     */
    public function sessionAttribute(string $key, mixed $value): self
    {
        $this->response->sessionAttributes[$key] = $value;
        return $this;
    }

    /**
     * Clear the output speech
     */
    public function clearSpeech(): self
    {
        $this->responseBody->outputSpeech = null;
        return $this;
    }

    /**
     * Clear the reprompt
     */
    public function clearReprompt(): self
    {
        $this->responseBody->reprompt = null;
        return $this;
    }

    /**
     * Clear the card
     */
    public function clearCard(): self
    {
        $this->responseBody->card = null;
        return $this;
    }

    /**
     * Clear all directives
     */
    public function clearDirectives(): self
    {
        $this->responseBody->directives = [];
        return $this;
    }

    /**
     * Build and return the final Response object
     */
    public function build(): Response
    {
        return $this->response;
    }

    /**
     * Convenience method: create a simple response with text and end session
     */
    public static function respondAndEndSession(string $text): Response
    {
        return self::create()
            ->text($text)
            ->endSession(true)
            ->build();
    }

    /**
     * Convenience method: create a simple response with text and keep session
     */
    public static function respondAndKeepSession(string $text): Response
    {
        return self::create()
            ->text($text)
            ->keepSession()
            ->build();
    }

    /**
     * Convenience method: create a response that asks for something with reprompt
     */
    public static function ask(string $text, string $reprompt): Response
    {
        return self::create()
            ->text($text)
            ->reprompt($reprompt)
            ->keepSession()
            ->build();
    }

    /**
     * Convenience method: create a response with SSML and end session
     */
    public static function respondSsmlAndEndSession(string $ssml): Response
    {
        return self::create()
            ->ssml($ssml)
            ->endSession(true)
            ->build();
    }

    /**
     * Convenience method: create a response with SSML and keep session
     */
    public static function respondSsmlAndKeepSession(string $ssml): Response
    {
        return self::create()
            ->ssml($ssml)
            ->keepSession()
            ->build();
    }

    /**
     * Convenience method: create a response with SSML and reprompt
     */
    public static function askSsml(string $ssml, string $repromptSsml): Response
    {
        return self::create()
            ->ssml($ssml)
            ->repromptSsml($repromptSsml)
            ->keepSession()
            ->build();
    }
}
