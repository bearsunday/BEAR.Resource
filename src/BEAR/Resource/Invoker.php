<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowed;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Di\Scope;
use Ray\Di\Definition;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Resource request invoker
 *
 * @Scope("singleton")
 */
class Invoker implements InvokerInterface
{
    /**
     * @var Linker
     */
    private $linker;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var NamedParameter
     */
    protected $params;

    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

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
     * @return $this
     * @Inject(optional=true)
     */
    public function setResourceLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param LinkerInterface           $linker
     * @param NamedParameter            $params
     * @param LoggerInterface           $logger
     * @param ExceptionHandlerInterface $exceptionHandler
     *
     * @Inject
     */
    public function __construct(
        LinkerInterface $linker,
        NamedParameter  $params,
        LoggerInterface $logger = null,
        ExceptionHandlerInterface $exceptionHandler = null
    ) {
        $this->linker = $linker;
        $this->params = $params;
        $this->logger = $logger;
        $this->exceptionHandler = $exceptionHandler ?: new ExceptionHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function invoke(Request $request)
    {
        $onMethod = 'on' . ucfirst($request->method);
        if (method_exists($request->ro, $onMethod) !== true) {
            return $this->methodNotExists($request->ro, $request, $onMethod);
        }
        // invoke with Named param and Signal param
        $args = $this->params->getArgs([$request->ro, $onMethod], $request->query);

        $result = null;
        try {
            $result = call_user_func_array([$request->ro, $onMethod], $args);
        } catch (Exception\Parameter $e) {
            $e =  new Exception\ParameterInService('', 0, $e);
            $result = $this->exceptionHandler->handle($e, $request);
        } catch (\Exception $e) {
            $result = $this->exceptionHandler->handle($e, $request);
        }

        if (!$result instanceof ResourceObject) {
            $request->ro->body = $result;
            $result = $request->ro;
        }

        // link
        completed:
        if ($request->links) {
            $result = $this->linker->invoke($request);
        }

        // log
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
     * @param ResourceObject $ro
     * @param Request        $request
     * @param                $method
     *
     * @return ResourceObject
     * @throws Exception\MethodNotAllowed
     */
    private function methodNotExists(ResourceObject $ro, Request $request, $method)
    {
        if ($request->method === self::OPTIONS) {
            return $this->onOptions($ro);
        }
        if ($method === 'onHead' && method_exists($ro, 'onGet')) {
            return $this->onHead($request);
        }

        throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
    }

    /**
     * @param ResourceObject $ro resource object
     *
     * @return ResourceObject
     */
    private function onOptions(ResourceObject $ro)
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
     * @return ResourceObject
     * @throws Exception\ParameterInService
     */
    private function onHead(Request $request)
    {
        if (method_exists($request->ro, 'onGet')) {
            // invoke with Named param and Signal param
            $args = $this->params->getArgs([$request->ro, 'onGet'], $request->query);
            try {
                call_user_func_array([$request->ro, 'onGet'], $args);
            } catch (Exception\Parameter $e) {
                throw new Exception\ParameterInService('', 0, $e);
            }
        }
        $request->ro->body = '';

        return $request->ro;
    }

    /**
     * {@inheritdoc}
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->params->attachParamProvider($varName, $provider);
    }

    /**
     * {@inheritdoc}
     */
    public function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }
}
