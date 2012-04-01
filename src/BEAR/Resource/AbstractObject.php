<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

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
    public $headers = [];

    /**
     * Resource body
     *
     * @var mixed
     */
    public $body;

    /**
     * Resource representation
     *
     * @var string
     */
    public $representation;

    /**
     * Renderer
     *
     * @var string
     */
    private $renderer = '';

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
     * Set renderer
     *
     * @param Stringer $stringer
     *
     * @Inject(optional = true)
     */
    public function setRederer(Renderable $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Transfer
     *
     * Transfer representational state
     */
    public function send()
    {
        $this->sender->send($this);
    }

    /**
     * Return representational string
     *
     * Return object hash if representation renderer is not set.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->renderer) {
            if (is_null($this->representation)) {
                $this->representation = $this->renderer->render($this);
            }
            $string = $this->representation;
        } else {
            $string = get_class($this) . '#' . md5(serialize($this->body));
        }
        return $string;
    }

}