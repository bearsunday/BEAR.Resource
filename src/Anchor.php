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
            $isValidLinkAnnotation = $annotation instanceof LinkAnnotation && isset($annotation->rel) && $annotation->rel === $rel;
            if ($isValidLinkAnnotation) {
                $body = $request->resourceObject->body;
                $query = is_array($body) ? array_merge($body, $query) : [];
                $uri = \GuzzleHttp\uri_template($annotation->href, $query);

                return [$annotation->method, $uri];
            }
        }

        throw new LinkException("rel:{$rel} class:" . get_class($request->resourceObject));
    }
}
