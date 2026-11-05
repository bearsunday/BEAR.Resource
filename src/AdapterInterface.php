<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface AdapterInterface
{
    /**
     * Return new resource object
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function get(AbstractUri $uri): ResourceObject;
}
