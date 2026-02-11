<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\CanFulfill;

use Rboschin\AmazonAlexa\Response\ResponseBodyInterface;

class CanFulfillResponseBody implements ResponseBodyInterface
{
    public function __construct(
        public ?CanFulfillIntentResponse $canFulfillIntent = null
    ) {
    }

    public static function create(CanFulfillIntentResponse $canFulfillIntent): self
    {
        return new self($canFulfillIntent);
    }
}
