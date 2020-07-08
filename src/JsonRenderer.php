<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function array_key_exists;
use function error_log;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

final class JsonRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        if (! array_key_exists('Content-Type', $ro->headers)) {
            $ro->headers['Content-Type'] = 'application/json';
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
