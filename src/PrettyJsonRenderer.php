<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function array_key_exists;
use function error_log;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

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
