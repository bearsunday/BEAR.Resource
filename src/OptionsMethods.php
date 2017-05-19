<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\DocBlockFactory;
use Ray\Di\Di\Assisted;

final class OptionsMethods
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param ResourceObject $ro
     * @param string         $requestMethod
     *
     * @return array
     */
    public function __invoke(ResourceObject $ro, $requestMethod)
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
        if ((bool) $paramDoc) {
            $paramMetas['parameters'] = $paramDoc;
        }
        if ((bool) $required) {
            $paramMetas['required'] = $required;
        }
        $paramMetas = $this->ignoreAnnotatedPrameter($method, $paramMetas);

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

    /**
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
                'type' => $type
            ];
            $description = (string) $tag->getDescription();
            if ($description) {
                $params[$varName]['description'] = $description;
            }
        }

        return $params;
    }

    /**
     * Ignore @ Assisted @ ResourceParam parameter
     *
     * @return array
     */
    private function ignoreAnnotatedPrameter(\ReflectionMethod $method, array $paramMetas)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                unset($paramMetas['parameters'][$annotation->param]);
                $paramMetas['required'] = array_values(array_diff($paramMetas['required'], [$annotation->param]));
            }
            if ($annotation instanceof Assisted) {
                $paramMetas = $this->ignorreAssisted($paramMetas, $annotation);
            }
        }

        return $paramMetas;
    }

    /**
     * Ignore @ Assisted parameter
     *
     * @return array
     */
    private function ignorreAssisted(array $paramMetas, Assisted $annotation)
    {
        $paramMetas['required'] = array_values(array_diff($paramMetas['required'], $annotation->values));
        foreach ($annotation->values as $varName) {
            unset($paramMetas['parameters'][$varName]);
        }

        return $paramMetas;
    }
}
