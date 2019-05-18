<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class JsonRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        if (! array_key_exists('content-type', $ro->headers)) {
            $ro->headers['content-type'] = 'application/json';
        }
        $ro->view = (string) json_encode($ro);
        $e = json_last_error();
        if ($e) {
            // @codeCoverageIgnoreStart
            $msg = json_last_error_msg();
            error_log('json_encode error: ' . $msg . ' in ' . __METHOD__);

            return '';
            // @codeCoverageIgnoreEnd
        }

        return $ro->view;
    }
}
