<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullUri extends AbstractUri
{
    /**
     * @var string
     */
    public $scheme = 'app';

    /**
     * @var string
     */
    public $host = 'self';

    /**
     * @var string
     */
    public $path = '/index';

    /**
     * Associative query array
     *
     * @var array
     */
    public $query = [];

    /**
     * @var string
     */
    public $method = 'get';
}
