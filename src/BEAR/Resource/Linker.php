<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Annotation\Link as AnnotationLink;
use BEAR\Resource\Exception\BadLinkRequest;
use Ray\Di\Di\Scope;
use Doctrine\Common\Annotations\Reader;
use SplQueue;
use ReflectionMethod;
use Ray\Di\Di\Inject;

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
     * Method name
     *
     * @var string
     */
    private $method;

    /**
     * Resource client
     *
     * @var ResourceInterface
     */
    private $resource;

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
     * @param \Doctrine\Common\Annotations\Reader $reader
     *
     * @Inject
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param SplQueue $q
     * @param LinkType $link
     *
     * @return array|null
     * @throws Exception\Link
     */
    private function getItem(\SplQueue $q, LinkType $link)
    {
        $cnt = $q->count();
        if ($cnt === 0) {
            return null;
        }
        $item = null;
        for ($i = 0; $i < $cnt; $i++) {
            list($item, $ro) = $q->dequeue();
            $request = $this->getLinkResult($ro, $link->key, (array)$item);
            if (!($request instanceof Request)) {
                throw new Exception\Link('From list to instance link is not currently supported.');
            }
            $requestResult = $request();
            /** @var $requestResult AbstractObject */
            $item[$link->key] = $requestResult->body;
            $item = (array)$item;
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     * @throws Exception\Link
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
            $item = $this->getItem($q, $link);
            if ($item) {
                continue;
            }
            if ($this->isList($refValue)) {
                foreach ($refValue as &$item) {
                    $request = $this->getLinkResult($ro, $link->key, $item);
                    /** @noinspection PhpUndefinedFieldInspection */
                    $requestResult = is_callable($request) ? $request()->body : $request;
                    $requestResult = is_array($requestResult) ? new \ArrayObject($requestResult) : $requestResult;
                    $item[$link->key] = $requestResult;
                    $q->enqueue([$requestResult, $request->ro]);
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
            /** @var $requestResult \BEAR\Resource\AbstractObject */
            switch ($link->type) {
                case LinkType::NEW_LINK:
                    if (!$hasTargeted) {
                        $sourceValue = [$sourceValue, $requestResult->body];
                        $hasTargeted = true;
                    } else {
                        $sourceValue[] = $requestResult->body;
                    }
                    $refValue = &$requestResult;
                    break;
                case LinkType::CRAWL_LINK:
                    $refValue[$link->key] = $requestResult->body;
                    $refValue = &$requestResult;
                    break;
                case LinkType::SELF_LINK:
                default:
                    $refValue = $requestResult->body;
            }
        }
        array_walk_recursive(
            $sourceValue,
            function (&$in) {
                if ($in instanceof \ArrayObject) {
                    $in = (array)$in;
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
     * @throws BadLinkRequest
     */
    private function getLinkResult($ro, $linkKey, $input)
    {
        $method = 'onLink' . ucfirst($linkKey);
        if (!method_exists($ro, $method)) {
            $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($ro, $this->method));
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AnnotationLink) {
                    if ($annotation->rel === $linkKey) {
                        $uri = $annotation->href;
                    }
                    $method = $annotation->method;
                    if ($input instanceof AbstractObject) {
                        $input = $input->body;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    $result = $this->resource->$method->uri($uri)->withQuery($input)->eager->request();

                    return $result;
                }
            }

            throw new BadLinkRequest(get_class($ro) . "::{$method}");
        }
        if (! $input instanceof AbstractObject) {
            $ro->body = $input;
            $input = $ro;
        }
        $result = call_user_func([$ro, $method], $input);

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
        $list = array_values((array)$list);
        $result = (count($list) > 1 && isset($list[0]) && isset($list[1]) && is_array($list[0]) && is_array(
            $list[1]
        ) && (array_keys($list[0]) === array_keys($list[1])));

        return $result;
    }

}
