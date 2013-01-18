<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Exception;

/**
 * Trait for resource string
 *
 * @package BEAR.Resource
 */
trait Render
{
    /**
     * Renderer
     *
     * @var \BEAR\Resource\Renderable
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
                error_log('Exception cached in ' . __METHOD__);
                error_log((string)$e);
            }
        } elseif (is_scalar($this->body)) {
            return (string)$this->body;
        } else {
            $view = '';
        }

        return $view;
    }
}
