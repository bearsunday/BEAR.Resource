<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\OptionsBody;
use ReflectionClass;
use ReflectionMethod;

use function implode;
use function in_array;
use function json_encode;
use function strtoupper;
use function substr;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

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
    /** @var OptionsMethods */
    private $optionsMethod;

    /** @var bool */
    private $optionsBody;

    /**
     * @OptionsBody("optionsBody")
     */
    public function __construct(OptionsMethods $optionsMethods, bool $optionsBody = true)
    {
        $this->optionsMethod = $optionsMethods;
        $this->optionsBody = $optionsBody;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $ro->headers['Content-Type'] = 'application/json';
        $allows = $this->getAllows((new ReflectionClass($ro))->getMethods());
        $ro->headers['Allow'] = implode(', ', $allows);
        $ro->view = $this->optionsBody ? json_encode($this->getEntityBody($ro, $allows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL : '';

        return $ro->view;
    }

    /**
     * Return allowed methods
     *
     * @param ReflectionMethod[] $methods
     *
     * @return string[]
     * @psalm-return list<string>
     */
    private function getAllows(array $methods): array
    {
        $allows = [];
        foreach ($methods as $method) {
            if (! in_array($method->name, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
                continue;
            }

            $allows[] = strtoupper(substr($method->name, 2));
        }

        return $allows;
    }

    /**
     * Return OPTIONS entity body
     *
     * @param list<string> $allows
     *
     * @return array<string, array<int|string, array|string>>
     */
    private function getEntityBody(ResourceObject $ro, array $allows): array
    {
        $mehtodList = [];
        foreach ($allows as $method) {
            $mehtodList[$method] = ($this->optionsMethod)($ro, $method);
        }

        return $mehtodList;
    }
}
