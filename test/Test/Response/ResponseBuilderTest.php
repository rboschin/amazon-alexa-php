<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Response;

use Rboschin\AmazonAlexa\Response\Card;
use Rboschin\AmazonAlexa\Response\OutputSpeech;
use Rboschin\AmazonAlexa\Response\Response;
use Rboschin\AmazonAlexa\Response\ResponseBuilder;
use Rboschin\AmazonAlexa\Response\Directives\Directive;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for ResponseBuilder
 */
class ResponseBuilderTest extends TestCase
{
    public function testCreateReturnsNewInstance(): void
    {
        $builder = ResponseBuilder::create();
        
        $this->assertInstanceOf(ResponseBuilder::class, $builder);
    }

    public function testTextReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->text('Hello world');
        
        $this->assertSame($builder, $result);
    }

    public function testTextSetsOutputSpeech(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello world')
            ->build();
        
        $this->assertInstanceOf(OutputSpeech::class, $response->response->outputSpeech);
        $this->assertEquals(OutputSpeech::TYPE_PLAINTEXT, $response->response->outputSpeech->type);
        $this->assertEquals('Hello world', $response->response->outputSpeech->text);
    }

    public function testSsmlReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->ssml('<speak>Hello</speak>');
        
        $this->assertSame($builder, $result);
    }

    public function testSsmlSetsOutputSpeech(): void
    {
        $ssml = '<speak>Hello world</speak>';
        $response = ResponseBuilder::create()
            ->ssml($ssml)
            ->build();
        
        $this->assertInstanceOf(OutputSpeech::class, $response->response->outputSpeech);
        $this->assertEquals(OutputSpeech::TYPE_SSML, $response->response->outputSpeech->type);
        $this->assertEquals($ssml, $response->response->outputSpeech->ssml);
    }

    public function testRepromptReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->reprompt('Try again');
        
        $this->assertSame($builder, $result);
    }

    public function testRepromptSetsReprompt(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->reprompt('Try again')
            ->build();
        
        $this->assertNotNull($response->response->reprompt);
        $this->assertInstanceOf(OutputSpeech::class, $response->response->reprompt->outputSpeech);
        $this->assertEquals('Try again', $response->response->reprompt->outputSpeech->text);
    }

    public function testRepromptSsmlReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->repromptSsml('<speak>Try again</speak>');
        
        $this->assertSame($builder, $result);
    }

    public function testRepromptSsmlSetsReprompt(): void
    {
        $ssml = '<speak>Try again</speak>';
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->repromptSsml($ssml)
            ->build();
        
        $this->assertNotNull($response->response->reprompt);
        $this->assertInstanceOf(OutputSpeech::class, $response->response->reprompt->outputSpeech);
        $this->assertEquals($ssml, $response->response->reprompt->outputSpeech->ssml);
    }

    public function testCardReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $card = new Card();
        $result = $builder->card($card);
        
        $this->assertSame($builder, $result);
    }

    public function testCardSetsCard(): void
    {
        $card = new Card(
            type: Card::TYPE_SIMPLE,
            title: 'Test Card',
            content: 'Test content'
        );
        
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->card($card)
            ->build();
        
        $this->assertSame($card, $response->response->card);
    }

    public function testDirectiveReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $directive = $this->createMock(Directive::class);
        $result = $builder->directive($directive);
        
        $this->assertSame($builder, $result);
    }

    public function testDirectiveAddsDirective(): void
    {
        $directive1 = $this->createMock(Directive::class);
        $directive2 = $this->createMock(Directive::class);
        
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->directive($directive1)
            ->directive($directive2)
            ->build();
        
        $this->assertCount(2, $response->response->directives);
        $this->assertSame($directive1, $response->response->directives[0]);
        $this->assertSame($directive2, $response->response->directives[1]);
    }

    public function testEndSessionReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->endSession(true);
        
        $this->assertSame($builder, $result);
    }

    public function testEndSessionSetsShouldEndSession(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->endSession(true)
            ->build();
        
        $this->assertTrue($response->response->shouldEndSession);
    }

    public function testKeepSessionReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->keepSession();
        
        $this->assertSame($builder, $result);
    }

    public function testKeepSessionSetsShouldEndSessionFalse(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->keepSession()
            ->build();
        
        $this->assertFalse($response->response->shouldEndSession);
    }

    public function testSessionAttributesReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $attributes = ['key1' => 'value1', 'key2' => 'value2'];
        $result = $builder->sessionAttributes($attributes);
        
        $this->assertSame($builder, $result);
    }

    public function testSessionAttributesSetsSessionAttributes(): void
    {
        $attributes = ['key1' => 'value1', 'key2' => 'value2'];
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->sessionAttributes($attributes)
            ->build();
        
        $this->assertEquals($attributes, $response->sessionAttributes);
    }

    public function testSessionAttributeReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->sessionAttribute('key', 'value');
        
        $this->assertSame($builder, $result);
    }

    public function testSessionAttributeAddsSingleAttribute(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->sessionAttribute('key1', 'value1')
            ->sessionAttribute('key2', 'value2')
            ->build();
        
        $expected = ['key1' => 'value1', 'key2' => 'value2'];
        $this->assertEquals($expected, $response->sessionAttributes);
    }

    public function testClearSpeechReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->text('Hello')->clearSpeech();
        
        $this->assertSame($builder, $result);
    }

    public function testClearSpeechRemovesOutputSpeech(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->clearSpeech()
            ->build();
        
        $this->assertNull($response->response->outputSpeech);
    }

    public function testClearRepromptReturnsSelf(): void
    {
        $builder = ResponseBuilder::create();
        $result = $builder->reprompt('Try again')->clearReprompt();
        
        $this->assertSame($builder, $result);
    }

    public function testClearRepromptRemovesReprompt(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->reprompt('Try again')
            ->clearReprompt()
            ->build();
        
        $this->assertNull($response->response->reprompt);
    }

    public function testClearCardReturnsSelf(): void
    {
        $card = new Card();
        $builder = ResponseBuilder::create();
        $result = $builder->card($card)->clearCard();
        
        $this->assertSame($builder, $result);
    }

    public function testClearCardRemovesCard(): void
    {
        $card = new Card();
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->card($card)
            ->clearCard()
            ->build();
        
        $this->assertNull($response->response->card);
    }

    public function testClearDirectivesReturnsSelf(): void
    {
        $directive = $this->createMock(Directive::class);
        $builder = ResponseBuilder::create();
        $result = $builder->directive($directive)->clearDirectives();
        
        $this->assertSame($builder, $result);
    }

    public function testClearDirectivesRemovesAllDirectives(): void
    {
        $directive = $this->createMock(Directive::class);
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->directive($directive)
            ->clearDirectives()
            ->build();
        
        $this->assertEmpty($response->response->directives);
    }

    public function testBuildReturnsResponse(): void
    {
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->build();
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRespondAndEndSession(): void
    {
        $response = ResponseBuilder::respondAndEndSession('Goodbye!');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Goodbye!', $response->response->outputSpeech->text);
        $this->assertTrue($response->response->shouldEndSession);
    }

    public function testRespondAndKeepSession(): void
    {
        $response = ResponseBuilder::respondAndKeepSession('Hello!');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Hello!', $response->response->outputSpeech->text);
        $this->assertFalse($response->response->shouldEndSession);
    }

    public function testAsk(): void
    {
        $response = ResponseBuilder::ask('What is your name?', 'Please tell me your name.');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('What is your name?', $response->response->outputSpeech->text);
        $this->assertEquals('Please tell me your name.', $response->response->reprompt->outputSpeech->text);
        $this->assertFalse($response->response->shouldEndSession);
    }

    public function testRespondSsmlAndEndSession(): void
    {
        $ssml = '<speak>Goodbye!</speak>';
        $response = ResponseBuilder::respondSsmlAndEndSession($ssml);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($ssml, $response->response->outputSpeech->ssml);
        $this->assertTrue($response->response->shouldEndSession);
    }

    public function testRespondSsmlAndKeepSession(): void
    {
        $ssml = '<speak>Hello!</speak>';
        $response = ResponseBuilder::respondSsmlAndKeepSession($ssml);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($ssml, $response->response->outputSpeech->ssml);
        $this->assertFalse($response->response->shouldEndSession);
    }

    public function testAskSsml(): void
    {
        $ssml = '<speak>What is your name?</speak>';
        $repromptSsml = '<speak>Please tell me your name.</speak>';
        $response = ResponseBuilder::askSsml($ssml, $repromptSsml);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($ssml, $response->response->outputSpeech->ssml);
        $this->assertEquals($repromptSsml, $response->response->reprompt->outputSpeech->ssml);
        $this->assertFalse($response->response->shouldEndSession);
    }

    public function testFluentChaining(): void
    {
        $card = new Card(
            type: Card::TYPE_SIMPLE,
            title: 'Test',
            content: 'Content'
        );
        $directive = $this->createMock(Directive::class);
        
        $response = ResponseBuilder::create()
            ->text('Hello')
            ->reprompt('Try again')
            ->card($card)
            ->directive($directive)
            ->sessionAttribute('key', 'value')
            ->keepSession()
            ->build();
        
        $this->assertEquals('Hello', $response->response->outputSpeech->text);
        $this->assertEquals('Try again', $response->response->reprompt->outputSpeech->text);
        $this->assertSame($card, $response->response->card);
        $this->assertSame($directive, $response->response->directives[0]);
        $this->assertEquals(['key' => 'value'], $response->sessionAttributes);
        $this->assertFalse($response->response->shouldEndSession);
    }
}
