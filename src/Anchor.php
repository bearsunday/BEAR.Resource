<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use Doctrine\Common\Annotations\AnnotationReader;
use Guzzle\Parser\UriTemplate\UriTemplateInterface;
use BEAR\Resource\Annotation;
use Ray\Di\Di\Inject;

/**
 * Anchor
 */
class Anchor
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var UriTemplateInterface
     */
    protected $uriTemplate;

    /**
     * @param UriTemplateInterface $uriTemplate
     * @param AnnotationReader     $reader
     * @param Request              $request
     *
     * @Inject
     */
    public function __construct(
        UriTemplateInterface $uriTemplate,
        AnnotationReader $reader,
        Request $request
    ) {
        $this->uriTemplate = $uriTemplate;
        $this->reader = $reader;
        $this->request = $request;
    }

    /**
     * Return linked request with hyper reference
     *
     * @param string  $rel
     * @param array   $query
     * @param Request $request
     *
     * @return Request
     * @throws Exception\Link
     */
    public function href($rel, Request $request, array $query)
    {
        $classMethod = 'on' . ucfirst($request->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($request->ro, $classMethod));
        foreach ($annotations as $annotation) {
            $isValidLinkAnnotation = $annotation instanceof Annotation\Link && isset($annotation->rel) && $annotation->rel === $rel;
            if ($isValidLinkAnnotation) {
                $body = $request->ro->body;
                $query = is_array($body) ? array_merge($body, $query) : [];
                $uri = $this->uriTemplate->expand($annotation->href, $query);

                return [$annotation->method, $uri];
            }
        }

        throw new Exception\Link("rel:{$rel} class:" . get_class($request->ro));
    }
}
