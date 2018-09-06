<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
