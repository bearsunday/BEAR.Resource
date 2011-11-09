<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface,
    Ray\Di\ConfigInterface,
    Ray\Di\ProviderInterface,
    Ray\Di\Injector;

/**
 * Invoker
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
    public function __construct(InjectorInterface $injector)
    {
        $this->config = $injector->getContainer()->getForge()->getConfig();
    }

    public function invoke(Request $request)
    {
        $methodName = "on{$request->method}";
        if (method_exists($request->ro, $methodName) !== true) {
            throw new Exception\InvalidMethod($request->method);
        }
        $params = $this->getParams($request->ro, $methodName, $request->query);
        try {
            $result = call_user_func_array(array($request->ro, $methodName), $params);
        } catch (\Exception $e) {

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
    private function getParams($object, $method, array $args)
    {
        $parameters = $this->config->getMethodReflect($object, $method)->getParameters();
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
