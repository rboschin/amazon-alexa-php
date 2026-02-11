<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Response\Directives\APL\StandardCommand;

use Rboschin\AmazonAlexa\Response\Directives\APL\StandardCommand\IdleCommand;
use PHPUnit\Framework\TestCase;

class IdleCommandTest extends TestCase
{
    public function testConstructor(): void
    {
        $command = new IdleCommand();

        $this->assertInstanceOf(IdleCommand::class, $command);
    }

    public function testJsonSerialize(): void
    {
        $command = new IdleCommand();
        $result = $command->jsonSerialize();

        $this->assertSame(IdleCommand::TYPE, $result['type']);
        $this->assertCount(1, $result);
    }

    public function testTypeConstant(): void
    {
        $this->assertSame('Idle', IdleCommand::TYPE);
    }
}
