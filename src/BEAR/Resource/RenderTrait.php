<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Exception;

/**
 * Trait for resource string
 */
trait RenderTrait
{
    /**
     * Renderer
     *
     * @var \BEAR\Resource\RenderInterface
     */
    protected $renderer;

    /**
     * Set renderer
     *
     * @param RenderInterface $renderer
     *
     * @return RenderTrait
     * @Ray\Di\Di\Inject(optional = true)
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
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
        /** @var $this AbstractObject */
        if (is_string($this->view)) {
            return $this->view;
        }
        if ($this->renderer instanceof RenderInterface) {
            try {
                $view = $this->renderer->render($this);
            } catch (Exception $e) {
                $view = '';
                error_log('Exception caught in ' . __METHOD__);
                error_log((string)$e);
            }
            return $view;
        }
        if (is_scalar($this->body)) {
            return (string)$this->body;
        }
        error_log('No renderer set for ' . get_class($this)  . ' in ' . __METHOD__);
        return '';
    }
}
