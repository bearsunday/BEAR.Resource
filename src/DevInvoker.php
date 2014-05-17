<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\Weave;
use Ray\Aop\Bind;
use Ray\Aop\WeavedInterface;
use XHProfRuns_Default;
use Ray\Di\Di\Scope;

/**
 * Resource request invoker
 *
 * @Scope("singleton")
 */
class DevInvoker extends Invoker implements InvokerInterface
{
    const HEADER_INTERCEPTORS = 'x-interceptors';

    const HEADER_EXECUTION_TIME = 'x-execution-time';

    const HEADER_MEMORY_USAGE = 'x-memory-usage';

    const HEADER_PROFILE_ID = 'x-profile-id';

    const HEADER_PARAMS = 'x-params';

    const HEADER_QUERY = 'x-query';

    /**
     * {@inheritDoc}
     */
    public function invoke(AbstractRequest $request)
    {
        $method = 'on' . ucfirst($request->method);

        $resource = $this->getRo($request);

        if ($request->method === self::OPTIONS || $request->method === self::HEAD) {
            $result = parent::invoke($request);

            return $result;
        }

        // MethodNotAllowed
        if ((!$request->ro instanceof Weave) && method_exists($request->ro, $method) !== true) {
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
        }
        return $this->devInvoke($resource, $request);
    }

    /**
     * @param ResourceObject   $resource
     * @param RequestInterface $request
     *
     * @return ResourceObject|mixed|null|resource|void
     */
    private function devInvoke(ResourceObject $resource, AbstractRequest $request)
    {
        $resource->headers[self::HEADER_QUERY] = json_encode($request->query);
        $time = microtime(true);
        $memory = memory_get_usage();
        if (extension_loaded('xhprof')) {
            /** @noinspection PhpUndefinedConstantInspection */
            /** @noinspection PhpUndefinedFunctionInspection */
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }
        // proceed original method
        /** @noinspection PhpUndefinedMethodInspection */
        $result = parent::invoke($request);

        // post process for log
        $time = microtime(true) - $time;
        $memory = memory_get_usage() - $memory;
        $resource->headers[self::HEADER_EXECUTION_TIME] = $time;
        $resource->headers[self::HEADER_MEMORY_USAGE] = $memory;
        if (extension_loaded('xhprof') && class_exists('XHProfRuns_Default', false)) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $xhprof = xhprof_disable();
            $profileId = (new XHProfRuns_Default)->save_run($xhprof, 'resource');
            $resource->headers[self::HEADER_PROFILE_ID] = $profileId;
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return ResourceObject
     */
    private function getRo(AbstractRequest $request)
    {
        if (!$request->ro instanceof WeavedInterface) {
            return $request->ro;
        }
        $ro = $request->ro;
        $bind = $ro->rayAopBind;
        /** @noinspection PhpUndefinedMethodInspection */
        $interceptors = $this->getBindInfo($bind);
        $ro->headers[self::HEADER_INTERCEPTORS] = json_encode($interceptors);

        return $request->ro;
    }

    /**
     * @param Bind $binds
     *
     * @return array
     */
    public function getBindInfo(Bind $binds)
    {
        $result = [];
        $iterator = $binds->getIterator();
        while ($iterator->valid()) {
            $method = $iterator->key();
            $interceptors = array_values($binds[$method]);
            foreach ($interceptors as &$interceptor) {
                $interceptor = get_class($interceptor);
            }
            $result[$method] = $interceptors;
            $iterator->next();
        }

        return $result;
    }
}
