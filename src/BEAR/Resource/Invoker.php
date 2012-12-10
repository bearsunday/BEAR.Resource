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
use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Annotation\ParamSignal;
use Ray\Di\Annotation;
use Ray\Aop\Weave;
use Ray\Aop\ReflectiveMethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\Scope;
use Ray\Di\Config;
use Ray\Di\Definition;

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
     * Provider annotation
     *
     * @var string
     */
    const ANNOTATION_PROVIDES = 'Provides';

    const SIGNAL_PARAM = 'param';

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
        Signal $signal
    ) {
        $this->config = $config;
        $this->linker = $linker;
        $this->signal = $signal;
    }

    /**
     * (non-PHPDoc)
     * @see \BEAR\Resource\InvokderInterface::setResourceClient()
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
     * (non-PHPdoc)
     * @see BEAR\Resource.InvokerInterface::invoke()
     * @throws Exception\Request
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        $isWeave = $request->ro instanceof Weave;
        if ($isWeave && $request->method !== Invoker::OPTIONS) {
            $weave = $request->ro;
            /** @noinspection PhpUnusedLocalVariableInspection */
            /** @var $weave Callable */
            $result = $weave([$this, 'getParams'], $method, $request->query);
            goto completed;
        }
        /** @var $request->ro \Ray\Aop\Weave */
        /** @noinspection PhpUndefinedMethodInspection */
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
        if ($this->logger instanceof LoggerInterface) {
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
     */
    public function getParams($object, $method, array $args)
    {
        $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        if ($parameters === []) {
            return [];
        }
        $providesArgs = [];
        $params = [];
        foreach ($parameters as $parameter) {
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
        /** @var $definition \Ray\Di\Definition  */
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
        // onFinalSync summarize all sync request data.
        /** @noinspection PhpUndefinedVariableInspection */
        $result = call_user_func([$request->ro, 'onFinalSync'], $request, $data);

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
