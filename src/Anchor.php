<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException;
use Override;
use Ray\Aop\ReflectionMethod;

use function array_merge;
use function assert;
use function is_array;
use function ucfirst;
use function uri_template;

/**
 * @psalm-import-type MethodUri from Types
 * @psalm-import-type Query from Types
 */
final class Anchor implements AnchorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws LinkException
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function href(string $rel, AbstractRequest $request, array $query): array
    {
        $classMethod = 'on' . ucfirst($request->method->value);
        $annotations = (new ReflectionMethod($request->resourceObject::class, $classMethod))->getAnnotations();
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                assert($annotation instanceof LinkAnnotation);

                return $this->getMethodUri($request, $query, $annotation);
            }
        }

        throw new LinkException("rel:{$rel} class:" . $request->resourceObject::class, 500);
    }

    /** @psalm-capabilities read-props */
    private function isValidLinkAnnotation(object $annotation, string $rel): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return $annotation instanceof LinkAnnotation && $annotation->rel !== null && $annotation->rel === $rel;
    }

    /**
     * @param Query $query
     *
     * @return MethodUri
     *
     * @psalm-impure
     */
    private function getMethodUri(AbstractRequest $request, array $query, LinkAnnotation $annotation): array
    {
        /** @var array|mixed $body */
        $body = $request->resourceObject->body;
        $query = is_array($body) ? array_merge($body, $query) : [];
        $uri = uri_template($annotation->href, $query);

        return [$annotation->method, $uri];
    }
}
