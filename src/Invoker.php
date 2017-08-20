<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use Ray\Di\Di\Named;

final class Invoker implements InvokerInterface
{
    /**
     * @var NamedParameterInterface
     */
    private $params;

    /**
     * @var RenderInterface
     */
    private $optionsRenderer;

    /**
     * @Named("optionsRenderer=options")
     */
    public function __construct(NamedParameterInterface $params, RenderInterface $optionsRenderer)
    {
        $this->params = $params;
        $this->optionsRenderer = $optionsRenderer;
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
            return $this->invokeOptions($request->resourceObject, $request, $onMethod);
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
     */
    private function postRequest(AbstractRequest $request, $result) : ResourceObject
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
     * @throws Exception\MethodNotAllowedException
     */
    private function invokeOptions(ResourceObject $ro, AbstractRequest $request, string $method) : ResourceObject
    {
        if ($request->method == Request::OPTIONS) {
            return $this->optionsRenderer->render($ro);
        }

        throw new MethodNotAllowedException(get_class($request->resourceObject) . "::$method()", 405);
    }
}
