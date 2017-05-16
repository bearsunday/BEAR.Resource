<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\DocBlockFactory;

/** @noinspection PhpInconsistentReturnPointsInspection */

/**
 * RFC2616 OPTIONS method renderer
 *
 * Set resource request information to `headers` and `view` in ResourceObject.
 *
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
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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
     * Return valid methods
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
     * @param ResourceObject $ro
     * @param array          $allows
     *
     * @return array
     */
    private function getEntityBody(ResourceObject $ro, $allows)
    {
        $mehtodList = [];
        foreach ($allows as $method) {
            $mehtodList[$method] = $this->getMethodParameters($ro, $method);
        }

        return $mehtodList;
    }

    /**
     * @param ResourceObject $ro
     * @param string         $method
     *
     * @return array
     */
    private function getMethodParameters(ResourceObject $ro, $requestMethod)
    {
        $method = new \ReflectionMethod($ro, 'on' . $requestMethod);
        $docComment = $method->getDocComment();
        $doc = $paramDoc = [];
        if ($docComment) {
            list($doc, $paramDoc) = $this->docBlock($docComment);
        }
        $parameters = $method->getParameters();
        list($paramDoc, $required) = $this->getParameterMetas($parameters, $paramDoc);
        $paramMetas = [];
        if ($paramDoc) {
            $paramMetas['parameters'] = $paramDoc;
        }
        if ($required) {
            $paramMetas['required'] = $required;
        }

        return $doc + $paramMetas;
    }

    /**
     * @param string $docComment
     *
     * @return array [$docs, $params]
     */
    private function docBlock($docComment)
    {
        $factory = DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);
        $summary = $docblock->getSummary();
        $docs = $params = [];
        if ($summary) {
            $docs['summary'] = $summary;
        }
        $description = (string) $docblock->getDescription();
        if ($description) {
            $docs['description'] = $description;
        }
        $tags = $docblock->getTagsByName('param');
        $params = $this->docBlogTags($tags, $params);

        return [$docs, $params];
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array                $paramDoc
     * @param string               $name
     *
     * @return string|null
     */
    private function getParameterType(\ReflectionParameter $parameter, array $paramDoc, $name)
    {
        $hasType = method_exists($parameter, 'getType') && $parameter->getType();
        if ($hasType) {
            return $this->getType($parameter);
        }
        if (isset($paramDoc[$name]['type'])) {
            return $paramDoc[$name]['type'];
        }
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array                  $paramDoc
     *
     * @return array [$paramDoc, $required]
     */
    private function getParameterMetas(array $parameters, array $paramDoc)
    {
        $required = [];
        foreach ($parameters as $parameter) {
            $paramDoc = $this->paramType($paramDoc, $parameter);
            if (! $parameter->isOptional()) {
                $required[] = $parameter->name;
            }
            $paramDoc = $this->paramDefault($paramDoc, $parameter);
        }

        return [$paramDoc, $required];
    }

    /**
     * @return array
     */
    private function paramDefault(array $paramDoc, \ReflectionParameter $parameter)
    {
        $hasDefault = $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() !== null;
        if ($hasDefault) {
            $paramDoc[$parameter->name]['default'] = (string) $parameter->getDefaultValue();
        }

        return $paramDoc;
    }

    /**
     * @return array
     */
    private function paramType(array $paramDoc, \ReflectionParameter $parameter)
    {
        $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
        if (is_string($type)) {
            $paramDoc[$parameter->name]['type'] = $type;
        }

        return $paramDoc;
    }

    /**
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    private function getType(\ReflectionParameter $parameter)
    {
        $type = (string) $parameter->getType();
        if ($type === 'int') {
            $type = 'integer';
        }

        return $type;
    }

    /*
     * @return array
     */
    private function docBlogTags(array $tags, array $params)
    {
        foreach ($tags as $tag) {
            /* @var $tag \phpDocumentor\Reflection\DocBlock\Tags\Param */
            $varName = $tag->getVariableName();
            $tagType = (string) $tag->getType();
            $type = $tagType === 'int' ? 'integer' : $tagType;
            $params[$varName] = [
                'description' => (string) $tag->getDescription(),
                'type' => $type
            ];
        }

        return $params;
    }
}
