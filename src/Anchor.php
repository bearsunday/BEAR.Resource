<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException;
use BEAR\Resource\Exception\UriException;
use Override;
use Ray\Aop\ReflectionMethod;

use function array_merge;
use function assert;
use function is_array;
use function sprintf;
use function str_contains;
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
     * @throws UriException
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
        return $annotation instanceof LinkAnnotation && $annotation->rel === $rel;
    }

    /**
     * @param Query $query
     *
     * @return MethodUri
     *
     * @throws UriException
     */
    private function getMethodUri(AbstractRequest $request, array $query, LinkAnnotation $annotation): array
    {
        /** @var array|mixed $body */
        $body = $request->resourceObject->body;
        $query = is_array($body) ? array_merge($body, $query) : [];
        $uri = uri_template($annotation->href, $query);
        // A variable can expand to a fragment, which a followed href does not have
        if (str_contains($uri, '#')) {
            throw new UriException(sprintf('%s: a #[Link] href cannot have a fragment', $uri), 500);
        }

        return [$annotation->method, $uri];
    }
}
