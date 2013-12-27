<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Ray\Di\Di\Inject;

/**
 * Abstract resource object
 */
abstract class ResourceObject implements ArrayAccess, Countable, IteratorAggregate
{
    // (array)
    use BodyArrayAccessTrait;

    // (string)
    use RenderTrait;

    /**
     * URI
     *
     * @var string
     */
    public $uri = '';

    /**
     * Resource status code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Resource header
     *
     * @var array
     */
    public $headers = [];

    /**
     * Resource representation
     *
     * @var string
     */
    public $view;

    /**
     * Resource links
     *
     * @var array
     */
    public $links = [];
}
