<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Di\ConfigInterface;
use Aura\Signal\Manager as Signal;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Annotation\ParamSignal;
use BEAR\Resource\Exception\MethodNotAllowed;
use Ray\Aop\Weave;
use Ray\Aop\ReflectiveMethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Scope;
use Ray\Di\Config;
use Ray\Di\Definition;
use ReflectionException;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

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
     * @var \Ray\Di\Config
     */
    private $config;

    /**
     * @var \BEAR\Resource\Linker
     */
    private $linker;

    /**
     * @var \Aura\Signal\Manager
     */
    private $signal;

    /**
     * Logger
     *
     * @var \BEAR\Resource\Logger
     */
    private $logger;

    /**
     * Method OPTIONS
     *
     * @var string
     */
    const OPTIONS = 'options';

    /**
     * Method HEAD
     *
     * @var string
     */
    const HEAD = 'head';

    /**
     * ProviderInterface annotation
     *
     * @var string
     */
    const ANNOTATION_PROVIDES = 'Provides';

    const SIGNAL_PARAM = 'param';

    /**
     * Return signal manager
     *
     * @return \Aura\Signal\Manager
     */
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * Constructor
     *
     * @param \Aura\Di\ConfigInterface $config
     * @param LinkerInterface          $linker
     * @param \Aura\Signal\Manager     $signal
     *
     * @Inject
     */
    public function __construct(
        ConfigInterface $config,
        LinkerInterface $linker,
        Signal $signal,
        ReflectiveParams $params = null
    ) {
        $this->config = $config;
        $this->linker = $linker;
        $this->signal = $signal;
        $this->params = $params ? : new ReflectiveParams($config, $signal, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function setResourceClient(ResourceInterface $resource)
    {
        $this->linker->setResource($resource);
    }

    /**
     * Resource logger setter
     *
     * @param LoggerInterface $logger
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
     * {@inheritDoc}
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        $isWeave = $request->ro instanceof Weave;
        /** @var $request->ro \Ray\Aop\Weave */
        /** @noinspection PhpUndefinedMethodInspection */
        $ro = $isWeave ? $request->ro->___getObject() : $request->ro;
        if ($isWeave && $request->method !== Invoker::OPTIONS && $request->method !== Invoker::HEAD) {
            $weave = $request->ro;
            /** @noinspection PhpUnusedLocalVariableInspection */
            /** @var $weave Callable */
            $result = $weave([$this->params, 'getParams'], $method, $request->query);
            goto completed;
        }
        if (method_exists($ro, $method) !== true) {
            return $this->methodNotExists($ro, $request, $method);
        }
        $params = $this->params->getParams($ro, $method, $request->query);
        try {
            $result = call_user_func_array([$ro, $method], $params);
        } catch (\Exception $e) {
            // @todo implements "Exception signal"
            throw $e;
        }
        // link
        completed:
        if ($request->links) {
            $result = $this->linker->invoke($ro, $request, $result);
        }
        if (!$result instanceof AbstractObject) {
            $ro->body = $result;
            $result = $ro;
            if ($result instanceof Weave) {
                $result = $result->___getObject();
            }

        }
        // request / result log
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($request, $result);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
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
     * @throws MethodNotAllowed
     */
    public function getParams($object, $method, array $args)
    {
        try {
            $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        } catch (ReflectionException $e) {
            throw new MethodNotAllowed;
        }
        if ($parameters === []) {
            return [];
        }
        $providesArgs = [];
        $params = [];
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } elseif (isset($providesArgs[$parameter->name])) {
                $params[] = $providesArgs[$parameter->name];
            } else {
                $result = $this->getArgumentBySignal($parameter, $object, $method, $args);
                if ($result->args) {
                    $providesArgs = $result->args;
                }
                $params[] = $result->value;
            }
        }

        return $params;
    }

    /**
     * Return argument from provider or signal handler
     *
     * This method called when client and service object both has sufficient argument
     *
     * @param \ReflectionParameter $parameter
     * @param  object              $object
     * @param string               $method
     * @param array                $args
     *
     * @return Result
     * @throws Exception\Parameter
     */
    private function getArgumentBySignal(ReflectionParameter $parameter, $object, $method, array $args)
    {
        $definition = $this->config->fetch(get_class($object))[Config::INDEX_DEFINITION];
        /** @var $definition \Ray\Di\Definition */
        $userAnnotation = $definition->getUserAnnotationByMethod($method);
        $signalAnnotations = isset($userAnnotation['ParamSignal']) ? $userAnnotation['ParamSignal'] : [];
        $signalIds = ['Provides'];
        foreach ($signalAnnotations as $annotation) {
            if ($annotation instanceof ParamSignal) {
                $signalIds[] = $annotation->value;
            }
        }
        $return = new Result;
        if (!$signalIds) {
            goto PARAMETER_NOT_PROVIDED;
        }
        foreach ($signalIds as $signalId) {
            $results = $this->signal->send(
                $this,
                self::SIGNAL_PARAM . $signalId,
                $return,
                $parameter,
                new ReflectiveMethodInvocation([$object, $method], $args, $signalAnnotations),
                $definition
            );
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $isStopped = $results->isStopped();
        $result = $results->getLast();
        if ($isStopped === false || is_null($result)) {
            goto PARAMETER_NOT_PROVIDED;
        }
        PARAMETER_PROVIDED:

        return $return;

        PARAMETER_NOT_PROVIDED:
        /** @noinspection PhpUnreachableStatementInspection */
        $msg = '$' . "{$parameter->name} in " . get_class($object) . '::' . $method;
        throw new Exception\Parameter($msg);
    }

    /**
     * {@inheritDoc}
     */
    public function invokeSync(\SplObjectStorage $requests)
    {
        $requests->rewind();
        $data = new \ArrayObject();
        while ($requests->valid()) {
            // each sync request method call.
            $request = $requests->current();
            if (method_exists($request->ro, 'onSync')) {
                call_user_func([$request->ro, 'onSync'], $request, $data);
            }
            $requests->next();
        }
        // onFinalSync summarize all sync request data.
        /** @noinspection PhpUndefinedVariableInspection */
        $result = call_user_func([$request->ro, 'onFinalSync'], $request, $data);

        return $result;
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
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        $params = [];
        foreach ($allow as $method) {
            $refMethod = new \ReflectionMethod($ro, 'on' . $method);
            $parameters = $refMethod->getParameters();
            $paramArray = [];
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $param = $parameter->isOptional() ? "({$name})" : $name;
                $paramArray[] = $param;
            }
            $key = "param-{$method}";
            $params[$key] = implode(',', $paramArray);
        }
        $result = ['allow' => $allow, 'params' => $params];

        return $result;
    }

    /**
     * @param AbstractObject $ro
     * @param Request        $request
     * @param                $method
     *
     * @return AbstractObject
     * @throws Exception\MethodNotAllowed
     */
    private function methodNotExists(AbstractObject $ro, Request $request, $method)
    {
        if ($request->method === self::OPTIONS) {
            return $this->onOptions($ro);
        }
        if ($method === 'onHead' && method_exists($ro, 'onGet')) {
            return $this->onHead($request);
        } else {
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
        }
    }

    /**
     * @param AbstractObject $ro resource object
     *
     * @return AbstractObject
     */
    private function onOptions(AbstractObject $ro)
    {
        $options = $this->getOptions($ro);
        $ro->headers['allow'] = $options['allow'];
        $ro->headers += $options['params'];
        $ro->body = null;

        return $ro;
    }

    /**
     * @param Request $request
     *
     * @return AbstractObject
     */
    private function onHead(Request $request)
    {
        $ro = ($request->ro instanceof Weave) ? $request->ro->___getObject() :  $request->ro;
        if (method_exists($ro, 'onGet')) {
            $params = $this->getParams($ro, 'onGet', $request->query);
            call_user_func_array([$ro, 'onGet'], $params);
        }
        $ro->body = '';

        return $ro;
    }
}
