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
     */
    public function invoke(AbstractRequest $request) : ResourceObject
    {
        $onMethod = 'on' . ucfirst($request->method);
        if (! method_exists($request->resourceObject, $onMethod) === true) {
            return $this->methodNoExists($request);
        }
        $params = $this->params->getParameters([$request->resourceObject, $onMethod], $request->query);
        $response = call_user_func_array([$request->resourceObject, $onMethod], $params);
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        return $response;
    }

    private function methodNoExists(AbstractRequest $request) : ResourceObject
    {
        if ($request->method === Request::OPTIONS) {
            $ro = $request->resourceObject;
            $ro->view = $this->optionsRenderer->render($request->resourceObject);

            return $ro;
        }

        throw new MethodNotAllowedException(get_class($request->resourceObject) . "::{($request->method}()", 405);
    }
}
