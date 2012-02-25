<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Signal\Exception as AuraException;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;

use Ray\Aop\Weave,
    Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\ConfigInterface,
    Ray\Di\ProviderInterface,
    Ray\Di\Annotation,
    Ray\Di\Definition;
use BEAR\Resource\Object as ResourceObject;
use Aura\Signal\Manager as Signal;
use ReflectionParameter;

/**
 * conventional class for refference value.
 *
 * @see      http://stackoverflow.com/questions/295016/is-it-possible-to-pass-parameters-by-reference-using-call-user-func-array
 * @see      http://d.hatena.ne.jp/sotarok/20090826/1251312215
 * @internal only for Invoker
 */
final class Result
{
    public $value;
}

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
     * Config
     *
     * @var Ray\Di\Config
     */
    private $config;

    /**
     * @var BEAR\Resource\Linker
     */
    private $linker;

    /**
     * @var Aura\Signal\Manager
     */
    private $signal;

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


    const SIGNAL_PARAM = 'param';

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     *
     * @Inject
     */
    public function __construct(ConfigInterface $config, Linkable $linker, Signal $signal)
    {
        $this->config = $config;
        $this->linker = $linker;
        $this->signal = $signal;
    }

    /**
     * Return config
     *
     * @return \Ray\Di\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Invokable::invoke()
     * @throws Exception\InvalidRequest
     */
    public function invoke(Request $request)
    {
        //$method = 'on' . $request->method;
        $method = $this->getMethodByAnnotation($request);
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
     * @todo not to get method name here, preare istead.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getMethodByAnnotation(Request $request)
    {
        /** @todo change magic number 2 to 'definition' */
        $definition = $this->config->fetch(get_class($request->ro))[2];
        $requestMethod = ucfirst($request->method);
        // annotation based request method (@Get) > name based request method (onGet)
        $hasAnnotationMethod = isset($definition[Definition::BY_NAME]) && isset($definition[Definition::BY_NAME][$requestMethod][0]);
        if ($hasAnnotationMethod === true) {
            $method = $definition[Definition::BY_NAME][$requestMethod][0];
            $methodAnnotation = $definition[Definition::BY_METHOD][$method][$requestMethod][0];
        } else {
            $method = 'on' . $requestMethod;
        }
        return $method;
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
        $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        if ($parameters === array()) {
            return array();
        }
        foreach ($parameters as $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } else {
                try {
                    $params[] = $this->getArgumentBySignal($parameter, $object, $method, $args);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }
        return $params;
    }

    /**
     * Return argument from providver or signal handler
     *
     * Thie method called when client and service object both has no argument
     *
     * @param array $definition
     * @param object $object
     * @param string $method
     * @param array $args
     * @throws Exception\InvalidParameter
     */
    private function getArgumentBySignal(ReflectionParameter $parameter, $object, $method, array $args)
    {
        /** @todo rm magic number 2 = definition */
        $definition = $this->config->fetch(get_class($object))[2];
        $userAnnotation = $definition->getUserAnnotationByMethod($method);
        $signalAannotations = isset($userAnnotation['ParamSignal']) ? $userAnnotation['ParamSignal'] : [];
        $signalIds = ['Provides'];
        foreach ($signalAannotations as $annotation) {
            if ($annotation instanceof \BEAR\Resource\Annotation\ParamSignal) {
                $signalIds[] = $annotation->value;
            }
        }
        $return = new Result;
        foreach ($signalIds as $signalId) {
            $results = $this->signal->send(
                    $this,
                    self::SIGNAL_PARAM . $signalId,
                    $return, $parameter, new ReflectiveMethodInvocation([$object, $method], $args, $signalAannotations), $definition
            );
        }
        $isStoped = $results->isStopped();
        $result = $results->getLast();
        if ($isStoped === false || is_null($result)) {
            goto PARAMETER_NOT_PROVIDED;
        }
PARAMETER_PROVIDED:
        return $return->value;
PARAMETER_NOT_PROVIDED:
        $msg = '$' . "{$parameter->name} in " . get_class($object) . '::' . $method;
        throw new Exception\InvalidParameter($msg);
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

    /**
     * Return signal manager
     *
     * @return \Aura\Signal\Manager
     */
    public function getSignal()
    {
        return $this->signal;
    }
}
