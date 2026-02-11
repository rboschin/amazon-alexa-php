<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Request\Request\AlexaSkillEvent;

use Rboschin\AmazonAlexa\Helper\PropertyHelper;

class SkillDisabledBody
{
    public const PERSISTED = 'PERSISTED';
    public const NOT_PERSISTED = 'NOT_PERSISTED';

    /**
     * @param string|null $userInformationPersistenceStatus User information persistence status
     */
    public function __construct(
        public ?string $userInformationPersistenceStatus = null,
    ) {
    }

    public static function fromAmazonRequest(array $amazonRequest): self
    {
        return new self(
            userInformationPersistenceStatus: PropertyHelper::checkNullValueString($amazonRequest, 'userInformationPersistenceStatus'),
        );
    }
}
