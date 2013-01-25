<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 */
abstract class AbstractObject implements ObjectInterface, ArrayAccess, Countable, IteratorAggregate
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
