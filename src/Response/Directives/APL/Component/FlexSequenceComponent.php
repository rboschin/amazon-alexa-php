<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\APL\Component;

use Rboschin\AmazonAlexa\Request\ScrollDirection;
use Rboschin\AmazonAlexa\Response\Directives\APL\Component\Traits\ActionableComponentTrait;
use Rboschin\AmazonAlexa\Response\Directives\APL\Component\Traits\MultiChildComponentTrait;
use Rboschin\AmazonAlexa\Response\Directives\APL\Document\APLComponentType;
use Rboschin\AmazonAlexa\Response\Directives\APL\Document\FlexAlignItems;
use Rboschin\AmazonAlexa\Response\Directives\APL\Document\Snap;
use Rboschin\AmazonAlexa\Response\Directives\APL\StandardCommand\AbstractStandardCommand;

class FlexSequenceComponent extends APLBaseComponent implements \JsonSerializable
{
    use ActionableComponentTrait;
    use MultiChildComponentTrait;

    public const TYPE = APLComponentType::FLEX_SEQUENCE;

    /**
     * @param FlexAlignItems|null $alignItems Alignment for children in the cross-axis
     * @param bool $numbered When true, assign ordinal numbers to the FlexSequence children
     * @param AbstractStandardCommand[]|null $onScroll Commands to run when scrolling
     * @param ScrollDirection|null $scrollDirection The direction to scroll this FlexSequence
     * @param Snap|null $snap The alignment that the child components snap to when scrolling stops
     */
    public function __construct(
        public ?FlexAlignItems $alignItems = null,
        public bool $numbered = false,
        public ?array $onScroll = null,
        public ?ScrollDirection $scrollDirection = null,
        public ?Snap $snap = null,
    ) {
        parent::__construct(self::TYPE);
    }

    public function jsonSerialize(): array
    {
        $data = array_merge(
            parent::jsonSerialize(),
            $this->serializeActionableProperties(),
            $this->serializeMultiChildProperties()
        );

        if ($this->alignItems !== null) {
            $data['alignItems'] = $this->alignItems->value;
        }

        if ($this->numbered) {
            $data['numbered'] = $this->numbered;
        }

        if ($this->onScroll !== null && !empty($this->onScroll)) {
            $data['onScroll'] = $this->onScroll;
        }

        if ($this->scrollDirection !== null) {
            $data['scrollDirection'] = $this->scrollDirection->value;
        }

        if ($this->snap !== null) {
            $data['snap'] = $this->snap->value;
        }

        return $data;
    }
}
