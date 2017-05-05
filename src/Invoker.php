<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;

final class Invoker implements InvokerInterface
{
    /**
     * @var NamedParameterInterface
     */
    private $params;

    /**
     * @var OptionProviderInterface
     */
    private $optionProvider;

    public function __construct(NamedParameterInterface $params, OptionProviderInterface $optionProvider)
    {
        $this->params = $params;
        $this->optionProvider = $optionProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedException
     */
    public function invoke(AbstractRequest $request)
    {
        $onMethod = 'on' . ucfirst($request->method);
        if (method_exists($request->resourceObject, $onMethod) !== true) {
            return $this->extraMethod($request->resourceObject, $request, $onMethod);
        }
        if ($request->resourceObject->uri instanceof AbstractUri) {
            $request->resourceObject->uri->query = $request->query;
            $request->resourceObject->uri->method = $request->method;
        }
        $params = $this->params->getParameters([$request->resourceObject, $onMethod], $request->query);
        $result = call_user_func_array([$request->resourceObject, $onMethod], $params);

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
        if (! $result instanceof ResourceObject) {
            $request->resourceObject->body = $result;
            $result = $request->resourceObject;
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
     * @throws Exception\MethodNotAllowedException
     *
     * @return ResourceObject
     */
    private function extraMethod(ResourceObject $ro, AbstractRequest $request, $method)
    {
        if ($request->method !== Request::OPTIONS) {
            throw new MethodNotAllowedException(get_class($request->resourceObject) . "::$method()", 405);
        }
        $optionProvider = $this->optionProvider ?: new OptionProvider;

        return $optionProvider->get($ro);
    }
}
