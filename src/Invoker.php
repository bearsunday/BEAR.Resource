<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Scope;

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
     * @param OptionProviderInterface $optionProvider
     *
     * @Inject(optional=true)
     */
    public function setOptionProvider(OptionProviderInterface $optionProvider)
    {
        $this->optionProvider = $optionProvider;
    }

    /**
     * @param NamedParameterInterface $params
     */
    public function __construct(NamedParameterInterface $params) {
        $this->params = $params;
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
        $params = $this->params->getParameters([$request->ro, $onMethod], $request->query);
        if (isset($request->ro->uri->query)) {
            $request->ro->uri->query = $request->query;
        }
        $result = call_user_func_array([$request->ro, $onMethod], $params);

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

        return $result;
    }

    /**
     * OPTIONS
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
        if ($request->method !== self::OPTIONS) {
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
        }
        $optionProvider = $this->optionProvider ?: new OptionProvider;

        return $optionProvider->get($ro);
    }
}
