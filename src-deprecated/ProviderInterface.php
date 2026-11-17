<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @deprecated Not used by the library */
interface ProviderInterface
{
    /**
     * Return new resource object
     */
    public function get(AbstractUri $uri): ResourceObject;
}
