<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Response\Directives\Display;

use Rboschin\AmazonAlexa\Response\Directives\Directive;

class RenderTemplateDirective extends Directive
{
    public const TYPE = 'Display.RenderTemplate';

    public function __construct(
        public ?Template $template = null
    ) {
        parent::__construct(self::TYPE);
    }

    public static function create(Template $template): self
    {
        return new self($template);
    }
}
