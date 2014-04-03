<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class JsonRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $ro->view = @json_encode($ro);
        $e = json_last_error();
        if ($e) {
            // @codeCoverageIgnoreStart
            error_log('json_encode error: ' . json_last_error_msg() . ' in ' . __METHOD__);
            return '';
            // @codeCoverageIgnoreEnd
        }

        return $ro->view;
    }
}
