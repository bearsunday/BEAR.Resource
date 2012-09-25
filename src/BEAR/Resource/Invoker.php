<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object;
use Ray\Di\Di\Scope;
use Aura\Di\ConfigInterface;
use Aura\Signal\Manager as Signal;
use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Annotation\ParamSignal;
use Ray\Aop\Weave;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use ReflectionParameter;

/**
 * Resource request invoker
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Invoker implements InvokerInterface
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
     * Logger
     *
     * @var BEAR\Resource\Logger
     */
    private $logger;

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
    public function __construct(
        ConfigInterface $config,
        LinkerInterface $linker,
        Signal $signal
    ){
        $this->config = $config;
        $this->linker = $linker;
        $this->signal = $signal;
    }

    /**
     * Set resource client
     *
     * @param ResourceInterface $resource
     */
    public function setResourceClient(ResourceInterface $resource)
    {
        $this->linker->setResource($resource);
    }

    /**
     * Resource logger setter
     *
     * @param ResourceLoggerInterface $logger
     *
     * @Inject(optional=true)
     */
    public function setResourceLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
     * @see BEAR\Resource.InvokerInterface::invoke()
     * @throws Exception\InvalidRequest
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        $isWeave = $request->ro instanceof Weave;
        if ($isWeave && $request->method !== Invoker::OPTIONS) {
            $weave = $request->ro;
            $result = $weave(array($this, 'getParams'), $method, $request->query);
            goto completed;
        }
        $ro = $isWeave ? $request->ro->___getObject() : $request->ro;
        if (method_exists($ro, $method) !== true) {
            if ($request->method === self::OPTIONS) {
                $options = $this->getOptions($ro);
                $ro->headers['allow'] = $options['allow'];
                $ro->headers += $options['params'];
                $ro->body = null;

                return $ro;
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
        completed:
        if ($request->links) {
            $result = $this->linker->invoke($request->ro, $request, $result);
        }
        // request / result log
        if ($this->logger) {
            $this->logger->log($request, $result);
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.InvokerInterface::invokeTraversal()
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
     * @param array  $args
     *
     * @return array
     * @throws Exception\InvalidParameter
     */
    public function getParams($object, $method, array $args)
    {
        $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        if ($parameters === []) {
            return [];
        }
        $providesArgs = [];
        foreach ($parameters as $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } elseif (isset($providesArgs[$parameter->name])) {
                $params[] = $providesArgs[$parameter->name];
            } else {
                try {
                    $result = $this->getArgumentBySignal($parameter, $object, $method, $args);
                    if ($result->args) {
                        $providesArgs = $result->args;
                    }
                    $params[] = $result->value;
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
     * @param array  $definition
     * @param object $object
     * @param string $method
     * @param array  $args
     *
     * @return mixed
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
            if ($annotation instanceof ParamSignal) {
                $signalIds[] = $annotation->value;
            }
        }
        $return = new Result;
        foreach ($signalIds as $signalId) {
            $results = $this->signal->send(
                    $this,
                    self::SIGNAL_PARAM . $signalId,
                    $return,
                    $parameter,
                    new ReflectiveMethodInvocation([$object, $method], $args, $signalAannotations),
                    $definition
            );
        }
        $isStoped = $results->isStopped();
        $result = $results->getLast();
        if ($isStoped === false || is_null($result)) {
            goto PARAMETER_NOT_PROVIDED;
        }
PARAMETER_PROVIDED:
        return $return;
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
    protected function getOptions(ResourceObject $ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allow = [];
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on')
            && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        $params = [];
        $paramArray = [];
        foreach ($allow as $method) {
            $refMethod = new \ReflectionMethod($ro, 'on' . $method);
            $parameters = $refMethod->getParameters();
            $paramArray = [];
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $param =  $parameter->isOptional() ? "({$name})" : $name;
                $paramArray[] = $param;
            }
            $key = "param-{$method}";
            $params[$key] = implode(',', $paramArray);
        }
        $result = ['allow' => $allow, 'params' => $params];

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.InvokerInterface::invokeSync()
     */
    public function invokeSync(\SplObjectStorage $requests)
    {
        $requests->rewind();
        $data = new \ArrayObject();
        while ($requests->valid()) {
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
