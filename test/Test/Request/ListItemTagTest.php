<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Test\Request;

use Rboschin\AmazonAlexa\Request\ListItemTag;
use PHPUnit\Framework\TestCase;

class ListItemTagTest extends TestCase
{
    public function testFromAmazonRequestWithIndex(): void
    {
        $tag = ListItemTag::fromAmazonRequest(['index' => 42]);

        $this->assertSame(42, $tag->index);
    }

    public function testFromAmazonRequestWithoutIndex(): void
    {
        $tag = ListItemTag::fromAmazonRequest([]);

        $this->assertNull($tag->index);
    }
}
