<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Request\Request\System;

use Rboschin\AmazonAlexa\Helper\PropertyHelper;
use Rboschin\AmazonAlexa\Request\Request\AbstractRequest;
use Rboschin\AmazonAlexa\Request\Request\Error;

class ExceptionEncounteredRequest extends SystemRequest
{
    public const TYPE = 'System.ExceptionEncountered';

    /**
     * @param \DateTime|null $timestamp Request timestamp
     * @param string|null $requestId Request identifier
     * @param string|null $locale Request locale
     * @param Error|null $error Error information
     * @param Cause|null $cause Cause information
     */
    public function __construct(
        ?\DateTime $timestamp = null,
        ?string $requestId = null,
        ?string $locale = null,
        public ?Error $error = null,
        public ?Cause $cause = null,
    ) {
        parent::__construct(
            type: self::TYPE,
            timestamp: $timestamp,
            requestId: $requestId,
            locale: $locale
        );
    }

    public static function fromAmazonRequest(array $amazonRequest): AbstractRequest
    {
        return new self(
            timestamp: self::getTime(PropertyHelper::checkNullValueStringOrInt($amazonRequest, 'timestamp')),
            requestId: PropertyHelper::checkNullValueString($amazonRequest, 'requestId'),
            locale: PropertyHelper::checkNullValueString($amazonRequest, 'locale'),
            error: isset($amazonRequest['error']) ? Error::fromAmazonRequest($amazonRequest['error']) : null,
            cause: isset($amazonRequest['cause']) ? Cause::fromAmazonRequest($amazonRequest['cause']) : null,
        );
    }
}
