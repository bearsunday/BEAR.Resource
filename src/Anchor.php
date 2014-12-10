<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link as LinkAnnotation;
use BEAR\Resource\Exception\LinkException as LinkException;
use Doctrine\Common\Annotations\Reader;

class Anchor implements AnchorInterface
{
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
    public function href($rel, AbstractRequest $request, array $query)
    {
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($request->resourceObject, $classMethod));
        foreach ($annotations as $annotation) {
            if ($this->isValidLinkAnnotation($annotation, $rel)) {
                return $this->getMethodUdi($request, $query, $annotation);
            }
        }

        throw new LinkException("rel:{$rel} class:" . get_class($request->resourceObject));
    }

    /**
     * @param LinkAnnotation $annotation
     * @param string         $rel
     *
     * @return bool
     */
    private function isValidLinkAnnotation($annotation, $rel)
    {
        return $annotation instanceof LinkAnnotation && isset($annotation->rel) && $annotation->rel === $rel;
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
