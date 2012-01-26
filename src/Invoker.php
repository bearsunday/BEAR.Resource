<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\Weave;

use Ray\Di\ConfigInterface,
Ray\Di\ProviderInterface;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource request invoker
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Invoker implements Invokable
{
    /**
     * Method OPTIONS
     *
     * @var string
     */
    const OPTIONS = 'options';

    /**
     * Provider annotation
     *
     * @var string
     */
    const ANNOTATION_PROVIDES = 'Provides';

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     *
     * @Inject
     */
    public function __construct(ConfigInterface $config, Linkable $linker)
    {
        $this->config = $config;
        $this->linker = $linker;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Invokable::invoke()
     * @throws Exception\InvalidRequest
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        if ($request->ro instanceof Weave) {
            $weave = $request->ro;
            return $weave(array($this, 'getParams'), $method, $request->query);
        }

        if (method_exists($request->ro, $method) !== true) {
            if ($request->method === self::OPTIONS) {
                return $this->getOptions($request->ro);
            }
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
        }
        $params = $this->getParams($request->ro, $method, $request->query);
        try {
            $result = call_user_func_array(array($request->ro, $method), $params);
        } catch (\Exception $e) {
            // @todo implements "Exception signal"
            throw $e;
        }
        // link
        if ($request->links) {
            $result = $this->linker->invoke($request->ro, $request->links, $result);
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Invokable::invokeTraversal()
     */
    public function invokeTraversal(\Traversable $requests)
    {
        foreach ($requests as &$element) {
            if ($element instanceof Request || is_callable($element)) {
                $element = $element();
            }
        }
        return $requests;
    }

    /**
     * Get named parameters
     *
     * @param object $object
     * @param string $method
     * @param array $args
     *
     * @return array
     * @throws Exception\InvalidParameter
     */
    public function getParams($object, $method, array $args)
    {
        $parameters = $this->config->getMethodReflect($object, $method)->getParameters();
        if ($parameters === array()) {
            return array();
        }
        foreach ($parameters as $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } else {
                $class = $parameter->getDeclaringFunction()->class;
                $method = $parameter->getDeclaringFunction()->name;
                $msg = '$' . "{$parameter->name} in {$class}::{$method}()";
                $provides = $this->config->fetch(get_class($object));
                if (!isset($provides[2]['user'][self::ANNOTATION_PROVIDES])) {
                    throw new Exception\InvalidParameter($msg);
                }
                $provides = $provides[2]['user'][self::ANNOTATION_PROVIDES];
                if (isset($provides[$parameter->name])) {
                    $method = $provides[$parameter->name];
                } elseif (isset($provides[""])) {
                    $method = $provides[''];
                    $result = call_user_func(array($object, $method));
                    if (!isset($result[$parameter->name])) {
                        throw new Exception\InvalidParameter($msg);
                    }
                    $params[] = $result[$parameter->name];
                } else {
                    throw new Exception\InvalidParameter($msg);
                }
                $params[] = call_user_func(array($object, $method));
            }
        }
        return $params;
    }

    /**
     * Return available resource request method
     *
     * @param ResourceObject $ro
     *
     * @return array
     */
    private function getOptions(ResourceObject $ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allows = array();
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on')
            && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allows[] = substr($method->name, 2);
            }
        }
        $params = array();
        foreach ($allows as $follow) {
            $paramArray = array();
            $refMethod = new \ReflectionMethod($ro, 'on' . $follow);
            $parameters = $refMethod->getParameters();
            foreach ($parameters as $parameter) {
                $paramArray[] = (string)$parameter;
            }
            $params = array($follow => implode(',', $paramArray));
        }
        $result = array('allows' => $allows, 'params' => $params);
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Invokable::invokeSync()
     */
    public function invokeSync(\SplObjectStorage $requests)
    {
        $requests->rewind();
        $data = new \ArrayObject();
        while($requests->valid()) {
            // each sync request method call.
            $request = $requests->current();
            if (method_exists($request->ro, 'onSync')) {
                call_user_func(array($request->ro, 'onSync'), $request, $data);
            }
            $requests->next();
        }
        //onFinalSync summaraize all sync request data.
        $result = call_user_func(array($request->ro, 'onFinalSync'), $request, $data);
        return $result;
    }
}
