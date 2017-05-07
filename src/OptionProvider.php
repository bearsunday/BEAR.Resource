<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\DocBlockFactory;

/** @noinspection PhpInconsistentReturnPointsInspection */

/**
 * OPTIONS resource request
 *
 * get($ro) return valid options request method response in 'application/json' media type.
 *
 * {
 *   "get": {
 *       "summary": "User",
 *       "description": "Returns a variety of information about the user specified by the required $id parameter",
 *       "parameters": {
 *           "id": {
 *               "description": "User ID",
 *               "type": "string",
 *               "required": true
 *           }
 *       }
 *   }
 *}
 */
final class OptionProvider implements OptionProviderInterface
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
    public function get(ResourceObject $ro)
    {
        $ro->headers['Content-Type'] = 'application/json';
        $allows = $this->getAllows((new \ReflectionClass($ro))->getMethods());
        $ro->headers['allow'] = implode(', ', $allows);
        $body = $this->getOptionsPayload($ro, $allows);
        $ro->view = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

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
    private function getOptionsPayload(ResourceObject $ro, $allows)
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
    private function getMethodParameters($ro, $requestMethod)
    {
        $method = new \ReflectionMethod($ro, 'on' . $requestMethod);
        $docComment = $method->getDocComment();
        $methodDoc = $paramDoc = [];
        if ($docComment) {
            list($methodDoc, $paramDoc) = $this->docBlock($docComment);
        }
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
            if (is_string($type)) {
                $paramDoc[$parameter->name]['type'] = $type;
            }
            $paramDoc[$parameter->name]['required'] = ! $parameter->isOptional();
        }
        $links = $this->getLink($method);
        $embeded = $this->getEmbeded($method);
        $response = [];
        if ($links) {
            $response['_links'] = $links;
        }
        if ($embeded) {
            $response['_embeded'] = $embeded;
        }

        return $methodDoc + ['parameters' => $paramDoc, 'response' => $response];
    }

    /**
     * @param string $docComment
     *
     * @return array [$docs, $params]
     */
    private function docBlock($docComment)
    {
        $docblock = (DocBlockFactory::createInstance())->create($docComment);
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
        foreach ($tags as $tag) {
            /* @var $tag \phpDocumentor\Reflection\DocBlock\Tags\Param */
            $varName = $tag->getVariableName();
            $params[$varName] = [
                'description' => (string) $tag->getDescription(),
                'type' => (string) $tag->getType()
            ];
        }

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
            return (string) $parameter->getType();
        }
        if (isset($paramDoc[$name]['type'])) {
            return $paramDoc[$name]['type'];
        }
    }

    /**
     * @return array
     */
    private function getLink(\ReflectionMethod $method)
    {
        $links = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $links[$annotation->rel] = ['href' =>$annotation->href];
            }
        }

        return $links;
    }

    /**
     * @return array
     */
    private function getEmbeded(\ReflectionMethod $method)
    {
        $embeded = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Embed) {
                $embeded[$annotation->rel] = $annotation->src;
            }
        }

        return $embeded;
    }
}
