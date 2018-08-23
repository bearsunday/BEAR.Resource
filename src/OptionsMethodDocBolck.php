<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use phpDocumentor\Reflection\DocBlockFactory;

final class OptionsMethodDocBolck
{
    /**
     * Return docBloc and parameter metas of method
     */
    public function __invoke(\ReflectionMethod $method) : array
    {
        $docComment = $method->getDocComment();
        $doc = $paramDoc = [];
        if ($docComment) {
            list($doc, $paramDoc) = $this->docBlock($docComment);
        }

        return [$doc, $paramDoc];
    }

    /**
     * @return array [$docs, $params]
     */
    private function docBlock(string $docComment) : array
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

    private function docBlogTags(array $tags, array $params) : array
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
}
