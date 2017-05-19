<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\Reader;

/**
 * RFC2616 OPTIONS method renderer
 *
 * Set resource request information to `headers` and `view` in ResourceObject.
 *
 * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
 * @see /docs/options/README.md
 */
final class OptionsRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var OptionsMethods
     */
    private $optionsMethod;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->optionsMethod = new OptionsMethods($reader);
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $ro->headers['Content-Type'] = 'application/json';
        $allows = $this->getAllows((new \ReflectionClass($ro))->getMethods());
        $ro->headers['allow'] = implode(', ', $allows);
        $body = $this->getEntityBody($ro, $allows);
        $ro->view = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        return $ro;
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
     *
     * @param ResourceObject $ro
     * @param array          $allows
     *
     * @return array
     */
    private function getEntityBody(ResourceObject $ro, $allows)
    {
        $mehtodList = [];
        foreach ($allows as $method) {
            $mehtodList[$method] = $this->optionsMethod->__invoke($ro, $method);
        }

        return $mehtodList;
    }
}
