<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\Weave;

use Ray\Di\ConfigInterface,
    Ray\Di\ProviderInterface;

/**
 * Resource request invoker
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Invoker implements Invoke
{
    /**
     * Constructor
     *
     * @param ConfigInterface $config
     *
     * @Inject
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        if ($request->ro instanceof Weave) {
            $weave = $request->ro;
            return $weave(array($this, 'getParams'), $method, $request->query);
        }

        if (method_exists($request->ro, $method) !== true) {
            throw new Exception\InvalidMethod(get_class($request->ro) . "::$method()");
        }
        $params = $this->getParams($request->ro, $method, $request->query);
        try {
            $result = call_user_func_array(array($request->ro, $method), $params);
        } catch (\Exception $e) {
            // @todo implements "Exception signal"
            throw new $e;
        }
        return $result;
    }

    /**
     * Get named parameters
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getParams($object, $method, array $args)
    {
        $parameters = $this->config->getMethodReflect($object, $method)->getParameters();
        if ($parameters === array()) {
            return array();
        }
        foreach($parameters as $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } else {
                $provides = $this->config->fetch(get_class($object));
                if (!isset($provides[2]['user']['Provide'])) {
                    goto error;
                }
                $provides = $provides[2]['user']['Provide'];
                if (isset($provides[$parameter->name])) {
                    $method = $provides[$parameter->name];
                } elseif (isset($provides[""])){
                    $method = $provides[''];
                    $result = call_user_func(array($object, $method));
                    if (!isset($result[$parameter->name])) {
                        goto error;
                    }
                    $params[] = $result[$parameter->name];
                } else {
                    goto error;
                }
                $params[] = call_user_func(array($object, $method));
            }
        }
        return $params;
error:
    throw new Exception\InvalidParameter($parameter->name);
    }
}
