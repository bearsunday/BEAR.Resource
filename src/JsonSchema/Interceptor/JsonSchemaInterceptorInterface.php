<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

interface JsonSchemaInterceptorInterface extends MethodInterceptor
{
    /**
     * {@inheritDoc}
     */
    public function invoke(MethodInvocation $invocation): ResourceObject;
}
