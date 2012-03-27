<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Di\ConfigInterface;

/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
abstract class AbstractObject implements Object, \ArrayAccess, \Countable, \IteratorAggregate
{
    use ArrayAccess;

    /**
     * URI
     *
     * @var string
     */
    public $uri = '';

    /**
     * Resource code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Resource header
     *
     * @var array
     */
    public $headers = array();

    /**
     * Resource body
     *
     * @var mixed
     */
    public $body;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->body = new \ArrayObject;
    }

    public function __wakeup()
    {
    }

    /**
     * Return resource object string.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . '#' . md5(serialize($this->body));
    }
}
