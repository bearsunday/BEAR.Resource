<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Adapter\App\Link;
use BEAR\Resource\Annotation\Link as AnnotationLink;
use BEAR\Resource\Exception\BadLinkRequest;
use Ray\Di\Config;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use Aura\Di\ConfigInterface;
use Doctrine\Common\Annotations\Reader;
use SplQueue;
use ReflectionMethod;

/**
 * Resource linker
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
final class Linker implements LinkerInterface
{
    /**
     * Set resource
     *
     * @param $resource $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     *
     * @Inject
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.LinkerInterface::invoke()
     * @throws Exception
     */
    public function invoke(ResourceObject $ro, Request $request, $sourceValue)
    {
        $this->method = 'on' . ucfirst($request->method);

        $links = $request->links;
        $hasTargeted = false;
        $refValue = &$sourceValue;
        $q = new SplQueue;
        $q->setIteratorMode(\SplQueue::IT_MODE_DELETE);

        // has links
        foreach ($links as $link) {
            $cnt = $q->count();
            if ($cnt !== 0) {
                $method = $request->method;
                for ($i = 0; $i < $cnt; $i++) {
                    list($item, $ro) = $q->dequeue();
                    $request = $this->getLinkResult($ro, $link->key, (array) $item);
                    if (!($request instanceof Request)) {
                        throw new Exception('From list to instance link is not currently supported.');
                    }
                    $ro = $request->ro;
                    $requestResult = $request();
                    $a = $requestResult;
                    $item[$link->key] = $a;
                    $item = (array) $item;
                }
                continue;
            }
            if ($this->isList($refValue)) {
                foreach ($refValue as &$item) {
                    $request = $this->getLinkResult($ro, $link->key, $item);
                    $requestResult = is_callable($request) ? $request() : $request;
                    $requestResult = is_array($requestResult) ? new \ArrayObject($requestResult) : $requestResult;
                    $item[$link->key] = $requestResult;
                    $q->enqueue(array($requestResult, $request->ro));
                }
                $refValue = &$requestResult;
                continue;
            }
            $request = $this->getLinkResult($ro, $link->key, $refValue);
            if (!($request instanceof Request)) {
                return $request;
            }
            $ro = $request->ro;
            $requestResult = $request();
            switch ($link->type) {
                case LINK::NEW_LINK:
                    if (!$hasTargeted) {
                        $sourceValue = array($sourceValue, $requestResult);
                        $hasTargeted = true;
                    } else {
                        $sourceValue[] = $requestResult;
                    }
                    $refValue = &$requestResult;
                    break;
                case LINK::CRAWL_LINK:
                    $refValue[$link->key] = $requestResult;
                    $refValue = &$requestResult;
                    break;
                case LINK::SELF_LINK:
                default:
                    $refValue = $requestResult;
                    break;
            }
        }
        array_walk_recursive(
                $sourceValue,
                function(&$in) {
            if ($in instanceof \ArrayObject) {
                $in = (array) $in;
            }
        }
        );

        return $sourceValue;
    }

    /**
     * Call link method
     *
     * @param mixed  $ro
     * @param string $linkKey
     * @param mixed  $input
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    private function getLinkResult($ro, $linkKey, $input)
    {
        $method = 'onLink' . ucfirst($linkKey);
        if (! method_exists($ro, $method)) {
            $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($ro, $this->method));
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AnnotationLink) {
                    if ($annotation->rel === $linkKey) {
                        $uri = $annotation->href;
                    }
                    $method = $annotation->method;
                    $result = $this->resource->$method->uri($uri)->withQuery($input)->eager->request();

                    return $result;
                }
            }

            throw new BadLinkRequest(get_class($ro) . "::{$method}");
        }
        $result = call_user_func(array($ro, $method), $input);

        return $result;
    }

    /**
     * Is data list ?
     *
     * @param mixed $list
     *
     * @return boolean
     */
    private function isList($list)
    {
        if (!(is_array($list))) {
            return false;
        }
        $list = array_values((array) $list);
        $result = (count($list) > 1
                && isset($list[0])
                && isset($list[1])
                && is_array($list[0])
                && is_array($list[1])
                && (array_keys($list[0]) === array_keys($list[1])));

        return $result;
    }

}
