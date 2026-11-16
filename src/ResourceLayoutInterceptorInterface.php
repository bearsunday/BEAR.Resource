<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Aop\MethodInterceptor;

/** Puts the parts of the resource's AbstractResourceLayout in the body before onGet() */
interface ResourceLayoutInterceptorInterface extends MethodInterceptor
{
}
