<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\ResourceObject;
use Override;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

interface JsonSchemaInterceptorInterface extends MethodInterceptor
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function invoke(MethodInvocation $invocation): ResourceObject;
}
