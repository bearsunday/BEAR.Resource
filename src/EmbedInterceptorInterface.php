<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Aop\MethodInterceptor;

/**
 * Interface for embed resource interceptor
 *
 * This interface allows alternative implementations (e.g., async/parallel)
 * to be swapped in via dependency injection.
 * An implementation sets `safeLinksOnly` on each src request it builds, so a link in the src that is not GET throws.
 */
interface EmbedInterceptorInterface extends MethodInterceptor
{
}
