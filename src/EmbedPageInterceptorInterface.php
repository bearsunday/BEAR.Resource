<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Aop\MethodInterceptor;

/** Embeds the parts of an AbstractEmbedPage attribute before onGet() */
interface EmbedPageInterceptorInterface extends MethodInterceptor
{
}
