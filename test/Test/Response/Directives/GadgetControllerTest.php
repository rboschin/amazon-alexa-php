<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Response\Directives;

use Rboschin\AmazonAlexa\Response\Directives\GadgetController\Animation;
use Rboschin\AmazonAlexa\Response\Directives\GadgetController\Parameters;
use Rboschin\AmazonAlexa\Response\Directives\GadgetController\Sequence;
use Rboschin\AmazonAlexa\Response\Directives\GadgetController\SetLightDirective;
use PHPUnit\Framework\TestCase;

class GadgetControllerTest extends TestCase
{
    public function testSetLightDirective(): void
    {
        $sequence = Sequence::create(100, 'FF0099');
        $animations = Animation::create([$sequence], 10, ['1']);
        $parameters = Parameters::create([$animations], Parameters::TRIGGER_EVENT_BUTTON_DOWN, 10);

        $sl = SetLightDirective::create(['gadgetId1', 'gadgetId2'], $parameters);
        $this->assertSame('GadgetController.SetLight', $sl->type);
        $this->assertSame(1, $sl->version);
        $this->assertSame(100, $sl->parameters->animations[0]->sequence[0]->durationMs);
    }
}
