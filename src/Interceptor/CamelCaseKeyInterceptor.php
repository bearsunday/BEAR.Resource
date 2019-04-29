<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

final class CamelCaseKeyInterceptor implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();
        /* @var ResourceObject $ro */
        $ro->body = $this->camelCaseKey($ro->body);

        return $invocation->proceed();
    }

    private function camelCaseKey($array) : array
    {
        $keys = array_map(function ($i) use (&$array) {
            if (is_array($array[$i])) {
                $array[$i] = $this->camelCaseKey($array[$i]);
            }
            $parts = explode('_', $i);

            return array_shift($parts) . implode('', array_map('ucfirst', $parts));
        }, array_keys($array));

        return array_combine($keys, $array);
    }
}
