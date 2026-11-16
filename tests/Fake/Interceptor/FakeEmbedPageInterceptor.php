<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\EmbedPageInterceptorInterface;
use Ray\Aop\MethodInvocation;

final class FakeEmbedPageInterceptor implements EmbedPageInterceptorInterface
{
    public function invoke(MethodInvocation $invocation)
    {
        $invocation->getThis()->body = ['swapped' => true];

        return $invocation->proceed();
    }
}
