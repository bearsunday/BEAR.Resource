<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException;
use Doctrine\Common\Annotations\Reader;
use function get_class;

final class Anchor implements AnchorInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LinkException
     */
    public function href(string $rel, AbstractRequest $request, array $query) : array
    {
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod(get_class($request->resourceObject), $classMethod));
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                assert($annotation instanceof LinkAnnotation);

                return $this->getMethodUri($request, $query, $annotation);
            }
        }

        throw new LinkException("rel:{$rel} class:" . get_class($request->resourceObject), 500);
    }

    private function isValidLinkAnnotation(object $annotation, string $rel) : bool
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return $annotation instanceof LinkAnnotation && $annotation->rel !== null && $annotation->rel === $rel;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array{0:string, 1:string}
     */
    private function getMethodUri(AbstractRequest $request, array $query, LinkAnnotation $annotation) : array
    {
        $body = $request->resourceObject->body;
        $query = is_array($body) ? array_merge($body, $query) : [];
        $uri = uri_template($annotation->href, $query);

        return [$annotation->method, $uri];
    }
}
