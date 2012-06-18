<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Countable;
use IteratorAggregate;

/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
abstract class AbstractObject implements Object, \ArrayAccess, Countable, IteratorAggregate
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
    public $view;

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
    }

    public function __wakeup()
    {
    }

    /**
     * Set renderer
     *
     * @param Renderable $renderer
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
        if (! $this->renderer) {
            return '';
        }
        if ($this->renderer) {
            if (is_null($this->view)) {
                try {
                    $this->view = $this->renderer->render($this);
                } catch (\Exception $e) {
                    $this->view = '';
                    error_log((string)$e);
                }
                $string = $this->view;
            } else {
                $string = get_class($this) . '#' . md5(serialize($this->body));
            }
            return $string;
        }
    }
}