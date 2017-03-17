<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException;
use Doctrine\Common\Annotations\Reader;

final class Anchor implements AnchorInterface
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
     *
     * @throws LinkException
     */
    public function href($rel, AbstractRequest $request, array $query)
    {
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($request->resourceObject, $classMethod));
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                return $this->getMethodUdi($request, $query, $annotation);
            }
        }

        throw new LinkException("rel:{$rel} class:" . get_class($request->resourceObject), 500);
    }

    /**
     * @param LinkAnnotation $annotation
     * @param string         $rel
     *
     * @return bool
     */
    private function isValidLinkAnnotation($annotation, $rel)
    {
        return $annotation instanceof LinkAnnotation && $annotation->rel !== null && $annotation->rel === $rel;
    }

    /**
     * @param AbstractRequest $request
     * @param array           $query
     * @param LinkAnnotation  $annotation
     *
     * @return array
     */
    private function getMethodUdi(AbstractRequest $request, array $query, LinkAnnotation $annotation)
    {
        $body = $request->resourceObject->body;
        $query = is_array($body) ? array_merge($body, $query) : [];
        $uri = uri_template($annotation->href, $query);

        return [$annotation->method, $uri];
    }
}
