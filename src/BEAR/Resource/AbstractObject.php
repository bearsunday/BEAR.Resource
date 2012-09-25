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
use Exception;

/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 */
abstract class AbstractObject implements Object, ArrayAccess, Countable, IteratorAggregate
{
    use BodyArrayAccess;

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
     * Resource links
     *
     * @var array
     */
    public $links = [];

    /**
     * Renderer
     *
     * @var BEAR\Resource\Renderable
     */
    protected $renderer;

    /**
     * Set renderer
     *
     * @param Renderable $renderer
     *
     * @Inject(optional = true)
     */
    public function setRenderer(Renderable $renderer)
    {
        $this->renderer = $renderer;
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
        if (is_string($this->view)) {
            return $this->view;
        }
        if ($this->renderer instanceof Renderable) {
            try {
                $view = $this->renderer->render($this);
            } catch (Exception $e) {
                $view = '';
                error_log('Exception catched in ' . __METHOD__);
                error_log((string) $e);
            }
        } else {
            $view = '';
        }

        return $view;
    }
}
