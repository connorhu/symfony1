<?php

namespace Symfony1\Components\Util;

use Symfony1\Components\Filter\Filter;
class FakeRenderingFilter extends Filter
{
    public function execute($filterChain)
    {
        $filterChain->execute();
        $this->context->getResponse()->sendContent();
    }
}
class_alias(FakeRenderingFilter::class, 'sfFakeRenderingFilter', false);