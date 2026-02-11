<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Request\Request\AlexaSkillEvent;

use Rboschin\AmazonAlexa\Helper\PropertyHelper;

class SkillAccountLinkedBody
{
    /**
     * @param string|null $accessToken Access token for the linked account
     */
    public function __construct(
        public ?string $accessToken = null,
    ) {
    }

    public static function fromAmazonRequest(array $amazonRequest): self
    {
        return new self(
            accessToken: PropertyHelper::checkNullValueString($amazonRequest, 'accessToken'),
        );
    }
}
