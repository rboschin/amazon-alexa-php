<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Request\Request\PlaybackController;

use Rboschin\AmazonAlexa\Helper\PropertyHelper;
use Rboschin\AmazonAlexa\Request\Request\AbstractRequest;

class PlayCommandIssued extends AbstractPlaybackController
{
    public const TYPE = 'PlaybackController.PlayCommandIssued';

    /**
     * @param \DateTime|null $timestamp Request timestamp
     * @param string|null $requestId Request identifier
     * @param string|null $locale Request locale
     */
    public function __construct(
        ?\DateTime $timestamp = null,
        ?string $requestId = null,
        ?string $locale = null,
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
        );
    }
}
