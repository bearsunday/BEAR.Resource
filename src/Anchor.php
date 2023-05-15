<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException;
use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;

use function array_merge;
use function assert;
use function is_array;
use function ucfirst;
use function uri_template;

final class Anchor implements AnchorInterface
{
    public function __construct(
        private Reader $reader,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws LinkException
     */
    public function href(string $rel, AbstractRequest $request, array $query): array
    {
        $classMethod = 'on' . ucfirst($request->method);
        /** @var list<object> $annotations */
        $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($request->resourceObject::class, $classMethod));
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                assert($annotation instanceof LinkAnnotation);

                return $this->getMethodUri($request, $query, $annotation);
            }
        }

        throw new LinkException("rel:{$rel} class:" . $request->resourceObject::class, 500);
    }

    private function isValidLinkAnnotation(object $annotation, string $rel): bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return $annotation instanceof LinkAnnotation && $annotation->rel !== null && $annotation->rel === $rel;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array{0:string, 1:string}
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
