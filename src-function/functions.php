<?php

if (! function_exists('\\uri_template')) {
    function uri_template($template, array $variables)
    {
        static $uriTemplate;

        if (! $uriTemplate) {
            $uriTemplate = new \Rize\UriTemplate();
        }

        return $uriTemplate->expand($template, $variables);
    }
}
