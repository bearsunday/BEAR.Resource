<?php

declare(strict_types=1);
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
        if (method_exists($request->ro, $onMethod) === true) {
            return $this->invokeMethod($request, $onMethod);
        }
        if ($request->method === Request::OPTIONS) {
            return $this->invokeOptions($request);
        }

        throw new MethodNotAllowedException(get_class($request->ro) . "::{($request->method}()", 405);
    }

    private function invokeMethod(AbstractRequest $request, string $onMethod) : ResourceObject
    {
        $params = $this->params->getParameters([$request->ro, $onMethod], $request->query);
        $response = call_user_func_array([$request->ro, $onMethod], $params);

        if (! $response instanceof ResourceObject) {
            $request->ro->body = $response;
            $response = $request->ro;
        }

        return $response;
    }

    private function invokeOptions(AbstractRequest $request) : ResourceObject
    {
        $ro = $request->ro;
        $ro->view = $this->optionsRenderer->render($request->ro);

        return $ro;
    }
}
