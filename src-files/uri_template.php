<?php

declare(strict_types=1);

if (! function_exists('\\uri_template')) {
    /**
     * Implementation of URI Template(RFC6570) specification for PHP
     *
     * @param string $template
     *
     * @return string
     *
     * @see http://pecl.php.net/package/uri_template
     */
    function uri_template($template, array $variables)
    {
        static $uriTemplate;

        if (! $uriTemplate) {
            $uriTemplate = new \Rize\UriTemplate();
        }

        return $uriTemplate->expand($template, $variables);
    }
}
