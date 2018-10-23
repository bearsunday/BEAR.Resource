<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * RFC2616 OPTIONS method renderer
 *
 * Set resource request information to `headers` and `view` in ResourceObject.
 *
 * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
 * @see /docs/options/README.md
 */
final class OptionsRenderer implements RenderInterface
{
    /**
     * @var OptionsMethods
     */
    private $optionsMethod;

    public function __construct(OptionsMethods $optionsMethods)
    {
        $this->optionsMethod = $optionsMethods;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $ro->headers['Content-Type'] = 'application/json';
        $allows = $this->getAllows((new \ReflectionClass($ro))->getMethods());
        $ro->headers['Allow'] = implode(', ', $allows);
        $body = $this->getEntityBody($ro, $allows);
        $ro->view = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        return $ro->view;
    }

    /**
     * Return allowed methods
     *
     * @param \ReflectionMethod[] $methods
     *
     * @return array
     */
    private function getAllows(array $methods)
    {
        $allows = [];
        foreach ($methods as $method) {
            if (in_array($method->name, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
                $allows[] = strtoupper(substr($method->name, 2));
            }
        }

        return $allows;
    }

    /**
     * Return OPTIONS entity body
     */
    private function getEntityBody(ResourceObject $ro, array $allows) : array
    {
        $mehtodList = [];
        foreach ($allows as $method) {
            $mehtodList[$method] = ($this->optionsMethod)($ro, $method);
        }

        return $mehtodList;
    }
}
