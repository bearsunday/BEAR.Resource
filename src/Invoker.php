<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Scope;

/**
 * Resource request invoker
 *
 * @Scope("Singleton")
 */
class Invoker implements InvokerInterface
{
    const METHOD_SYNC = 'onSync';

    const METHOD_FINAL_SYNC = 'onFinalSync';

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
     * @var
     */
    private $optionProvider;

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
     * @param OptionProviderInterface $optionProvider
     *
     * @Inject(optional=true)
     */
    public function setOptionProvider(OptionProviderInterface $optionProvider)
    {
        $this->optionProvider = $optionProvider;
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
    public function invoke(AbstractRequest $request)
    {
        $onMethod = 'on' . ucfirst($request->method);
        if (method_exists($request->ro, $onMethod) !== true) {
            return $this->extraMethod($request->ro, $request, $onMethod);
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

        return $this->postRequest($request, $result);
    }

    /**
     * @param AbstractRequest $request
     * @param mixed           $result
     *
     * @return ResourceObject
     */
    private function postRequest(AbstractRequest $request, $result)
    {
        if (!$result instanceof ResourceObject) {
            $request->ro->body = $result;
            $result = $request->ro;
        }
        if ($request->links) {
            $result = $this->linker->invoke($request);
        }
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
        $data = new \ArrayObject;
        $request = null;
        while ($requests->valid()) {
            // each sync request method call.
            $request = $requests->current();
            if (method_exists($request->ro, self::METHOD_SYNC)) {
                call_user_func([$request->ro, self::METHOD_SYNC], $request, $data);
            }
            $requests->next();
        }
        $result = call_user_func([$request->ro, self::METHOD_FINAL_SYNC], $request, $data);

        return $result;
    }


    /**
     * OPTIONS or HEAD
     *
     * @param ResourceObject  $ro
     * @param AbstractRequest $request
     * @param string          $method
     *
     * @return ResourceObject
     * @throws Exception\MethodNotAllowed
     */
    private function extraMethod(ResourceObject $ro, AbstractRequest $request, $method)
    {
        if ($request->method === self::OPTIONS) {
            $optionProvider = $this->optionProvider ?: new OptionProvider;
            return $optionProvider->get($ro);
        }
        if ($method === 'onHead' && method_exists($ro, 'onGet')) {
            return $this->onHead($request);
        }

        throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
    }
    /**
     * @param Request $request
     *
     * @return ResourceObject
     * @throws Exception\ParameterInService
     */
    private function onHead(AbstractRequest $request)
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
