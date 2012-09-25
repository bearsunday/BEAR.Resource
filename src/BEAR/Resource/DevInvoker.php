<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\Weave;
use Ray\Aop\Bind;
use XHProfRuns_Default;
use Ray\Di\Di\Scope;

/**
 * Resource request invoker
 *
 * @package BEAR.Resource
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
     * (non-PHPdoc)
     * @see BEAR\Resource.InvokerInterface::invoke()
     * @throws Exception\Request
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        $ro = ($request->ro instanceof Weave) ? $request->ro->___getObject() : $request->ro;
        // before process
        if ($request->ro instanceof Weave) {
            // interceptor binded object
            $bind = $request->ro->___getBind();
            $resource = $request->ro->___getObject();
            $interceptors = $this->getBindInfo($bind);
            $resource->headers[self::HEADER_INTERCEPTORS] = json_encode($interceptors);
        } else {
            // no bind.
            $resource = $request->ro;
        }

        // MethodNotAllowed
        if ((! $request->ro instanceof Weave) && method_exists($request->ro, $method) !== true) {
            if ($request->method === self::OPTIONS) {
                return $this->getOptions($request->ro);
            }
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::$method()", 405);
        }

        $resource->headers[self::HEADER_QUERY] = json_encode($request->query);
        $time = microtime(true);
        $memory = memory_get_usage();
        if (extension_loaded('xhprof')) {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }
        // proceed original method
        $ro = ($request->ro instanceof Weave) ? $request->ro->___getObject() : $request->ro;
        $params = $this->getParams($ro, $method, $request->query);
        $resource->headers[self::HEADER_PARAMS] = json_encode($params, true);
        $result = parent::invoke($request);

        // post process for log
        $time = microtime(true) - $time;
        $memory = memory_get_usage() - $memory;
        $resource->headers[self::HEADER_EXECUTION_TIME] = $time;
        $resource->headers[self::HEADER_MEMORY_USAGE] = $memory;
        if (extension_loaded('xhprof') && class_exists('XHProfRuns_Default', false)) {
            $xhprof = xhprof_disable();
            $profileId = (new XHProfRuns_Default)->save_run($xhprof, 'resource');
            $resource->headers[self::HEADER_PROFILE_ID] = $profileId;
        }

        return $result;
    }

    public function getBindInfo(Bind $binds)
    {
        $result = [];
        $binds = (array) $binds;
        foreach ($binds as $method => $bind) {
            $interceptors = array_values($binds[$method]);
            foreach ($interceptors as &$interceptor) {
                $interceptor = get_class($interceptor);
            }
            $result[$method] = $interceptors;
        }

        return $result;
    }
}
