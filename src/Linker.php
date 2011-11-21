<?php
/**
 * BEAR.Resource
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource linker
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Linker implements linkable
{
    public function __construct()
    {
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Linkable::invoke()
     */
    public function invoke(ResourceObject $ro, array $links, $sourceValue)
    {
        $hasTargeted = false;

        $refValue = &$sourceValue;
        $q = new \SplQueue;
        $q->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        foreach ($links as $link) {
            $cnt = $q->count();
            if ($cnt !== 0) {
                for ($i = 0; $i < $cnt ; $i++) {
                    list($item, $ro) = $q->dequeue();
                    $request = $this->callLinkMethod($ro, $link->key, (array)$item);
                    if (!($request instanceof Request)) {
                        throw new Exception('From list to instance link is not currently supported.');
                        $item[$link->key] = $request;
                    }
                    $ro = $request->ro;
                    $requestResult = $request();
                    $a = $requestResult;
                    $item[$link->key] = $a;
                    $item = (array)$item;
                }
                continue;
            }
            if ($this->isList($refValue)) {
                foreach ($refValue as &$item) {
                    $request = $this->callLinkMethod($ro, $link->key, $item);
                    $requestResult = is_callable($request) ? $request() : $request;
                    $requestResult = is_array($requestResult) ? new \ArrayObject($requestResult) : $requestResult;
                    $item[$link->key] = $requestResult;
                    $q->enqueue(array($requestResult, $request->ro));
                }
                $refValue = &$requestResult;
                continue;
            }
            $request = $this->callLinkMethod($ro, $link->key, $refValue);
            if (!($request instanceof Request)) {
                return $request;
            }
            $ro = $request->ro;
            $requestResult = $request();
            switch ($link->type) {
                case LINK::SELF_LINK:
                    $refValue = $requestResult;
                    break;
                case LINK::CRAWL_LINK:
                    $refValue[$link->key] = $requestResult;
                    $refValue = &$requestResult;
                    break;
                case LINK::NEW_LINK:
                    if (!$hasTargeted) {
                        $sourceValue = array($sourceValue, $requestResult);
                        $hasTargeted = true;
                    } else {
                        $sourceValue[] = $requestResult;
                    }
                    $refValue = &$requestResult;
            }
        }
        array_walk_recursive(
            $sourceValue,
            function(&$in) {
            if ($in instanceof \ArrayObject) {
                    $in = (array)$in;
                }
            }
        );
        return $sourceValue;
    }

    /**
     *
     * @param unknown_type $object
     * @param unknown_type $link
     * @param unknown_type $input
     * @throws \BadMethodCallException
     *
     * @return Request
     */
    private function callLinkMethod($ro, $linkKey, $input)
    {
        $method = 'onLink' . ucfirst($linkKey);
        if (!method_exists($ro, $method)) {
            throw new \BadMethodCallException(get_class($ro) . "::{$method}");
        }
        $result = call_user_func(array($ro, $method), $input);
        return $result;
    }

    /**
     * Is data list ?
     *
     * @param array $list
     * @return bool
     */
    private function isList($list)
    {
        if (!(is_array($list))) {
            return false;
        }
        $list = array_values((array)$list);
        $result = (count($list) > 1
            && isset($list[0])
            && isset($list[1])
            && is_array($list[0])
            && is_array($list[1])
            && (array_keys($list[0]) === array_keys($list[1])));
        return $result;
    }

}
