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
 * @internal
 * @psalm-import-type MethodUri from Types
 * @psalm-import-type Query from Types
 */
final class Anchor implements AnchorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws LinkException
     */
    #[Override]
    public function href(string $rel, AbstractRequest $request, array $query): array
    {
        $classMethod = 'on' . ucfirst($request->method->value);
        $annotations = (new ReflectionMethod($request->resourceObject::class, $classMethod))->getAnnotations();
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                assert($annotation instanceof LinkAnnotation);

                return $this->methodUri($annotation, $request->resourceObject->body, $query);
            }
        }

        throw new LinkException("rel:{$rel} class:" . $request->resourceObject::class, 500);
    }

    /** @psalm-capabilities read-props */
    private function isValidLinkAnnotation(object $annotation, string $rel): bool
    {
        return $annotation instanceof LinkAnnotation && $annotation->rel === $rel;
    }

    /**
     * The method a link declares and its href expanded with the body overlaid by $query
     *
     * Resource::href() and a self/new link of the Linker both request the linked resource with these.
     *
     * @param Query $query
     *
     * @return MethodUri
     */
    public function methodUri(LinkAnnotation $annotation, mixed $body, array $query): array
    {
        $query = is_array($body) ? array_merge($body, $query) : [];
        $uri = uri_template($annotation->href, $query);

        return [$annotation->method, $uri];
    }
}
