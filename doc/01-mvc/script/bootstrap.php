<?php
/**
 * Loader (to be replaced to autolodaer)
 */
namespace BEAR\Framework\Autload {
    $systemPath = dirname(dirname(dirname(__DIR__)));
    $appPath = dirname(__DIR__);
    require $systemPath . '/vendors/Ray.Aop/src.php';
    require $systemPath . '/vendors/Ray.Di/src.php';
    require $systemPath . '/src.php';
    require $appPath . '/Module/FrameWorkModule.php';
    require $appPath . '/Module/HelloModule.php';
    require $appPath . '/Module/ResourceAdapterProvider.php';
    require $appPath . '/Page/Hello.php';
    require $appPath . '/Page/HelloAop.php';
    require $appPath . '/ResourceObject/Greeting.php';
    require $appPath . '/ResourceObject/Greeting/Aop.php';
    require $appPath . '/Interceptor/Log.php';
}
/**
 * Framework
 */
namespace BEAR\Framework {
    class Dispatch
    {
        function getKey()
        {
            $options = getopt('u:', array('url:'));
            $dispatchKey = $options['url'];
            return $dispatchKey;
        }
    }
}

/**
 * Bootstrap
 *
 * bootstrap provides 3 instances by web context (URL)
 * $di (DiC), $resource (Resource client), $page (Page resource)
 */
namespace BEAR\Framework\Boot {

    use Ray\Di\Annotation,
        Ray\Di\Config,
        Ray\Di\Forge,
        Ray\Di\Container,
        Ray\Di\Injector,
        BEAR\Framework\FrameWorkModule,
        BEAR\Framework\Dispatch,
        helloWorld\Module\HelloModule;

    $dispatch = new Dispatch;
    $dispatchKey = $dispatch->getKey();

    $cacheFile = __DIR__ . "/cache/{$dispatchKey}.txt";
    $hasCache = file_exists($cacheFile);
    if ($hasCache === true) {
        echo "[Cached]\n";
        list($di, $resource, $page) = unserialize(file_get_contents($cacheFile));
    } else {
    // application fixed instance ($di, $resource)
        $di = new Injector(new Container(new Forge(new Config(new Annotation))));
        $module = new HelloModule(new FrameWorkModule($di));
        $di->setModule($module);
        $resource = $di->getInstance('BEAR\Resource\Client');

        // request URL based page resource instance ($page)
        $page = $resource->newInstance("page://self/{$dispatchKey}");
        file_put_contents($cacheFile, serialize(array($di, $resource, $page)));
    }
}