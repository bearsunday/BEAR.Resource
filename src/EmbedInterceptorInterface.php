<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Aop\MethodInterceptor;

/**
 * Interface for embed resource interceptor
 *
 * This interface allows alternative implementations (e.g., async/parallel)
 * to be swapped in via dependency injection.
 */
interface EmbedInterceptorInterface extends MethodInterceptor
{
}
