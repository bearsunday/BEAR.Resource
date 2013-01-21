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
     * @Inject(optional = true)
     */
    public function setRenderer(RenderInterface $renderer)
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
        if ($this->renderer instanceof RenderInterface) {
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
