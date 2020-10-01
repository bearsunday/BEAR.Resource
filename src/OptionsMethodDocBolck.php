<?php

declare(strict_types=1);

namespace BEAR\Resource;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;

final class OptionsMethodDocBolck
{
    /**
     * Return docBloc and parameter metas of method
     *
     * @return array{0: array{summary?: string, description?: string}, 1: array<string, array{type: string, description?: string}>}
     */
    public function __invoke(ReflectionMethod $method): array
    {
        $docComment = $method->getDocComment();
        $doc = $paramDoc = [];
        if ($docComment) {
            [$doc, $paramDoc] = $this->docBlock($docComment);
        }

        return [$doc, $paramDoc];
    }

    /**
     * @return (string|string[])[][]
     * @psalm-return array{0: array{summary?: string, description?: string}, 1: array<string, array{type: string, description?: string}>}
     */
    private function docBlock(string $docComment): array
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

        /** @var Param[] $tags */
        $tags = $docblock->getTagsByName('param');
        $params = $this->docBlogTags($tags, $params);

        return [$docs, $params];
    }

    /**
     * @param Param[]                                                  $tags
     * @param array<string, array{type: string, description?: string}> $params
     *
     * @return array<string, array{type: string, description?: string}>
     */
    private function docBlogTags(array $tags, array $params): array
    {
        foreach ($tags as $tag) {
            $varName = (string) $tag->getVariableName();
            $tagType = (string) $tag->getType();
            $type = $tagType === 'int' ? 'integer' : $tagType;
            $params[$varName] = ['type' => $type];
            $description = (string) $tag->getDescription();
            if (! $description) {
                continue;
            }

            $params[$varName]['description'] = $description;
        }

        return $params;
    }
}
