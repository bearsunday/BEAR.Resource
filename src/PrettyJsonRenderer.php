<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class PrettyJsonRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        if (! array_key_exists('Content-Type', $ro->headers)) {
            $ro->headers['Content-Type'] = 'application/json';
        }
        $ro->view = json_encode($ro, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
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
