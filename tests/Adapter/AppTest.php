<?php

namespace testworld\Resource\App\User {

    class Friend
    {
    }
}

namespace BEAR\Resource\Adapter {

    use Ray\Di\Definition,
        Ray\Di\Annotation,
        Ray\Di\Config,
        Ray\Di\Forge,
        Ray\Di\Container,
        Ray\Di\Manager,
        Ray\Di\Injector,
        Ray\Di\EmptyModule;
    use BEAR\Resource\Adapter\App as AppAdapter;

    class AppTest extends \PHPUnit_Framework_TestCase
    {
        protected $injector;
        protected $namespace;

        protected function setUp()
        {
            parent::setUp();
            $this->injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule());
            $this->namespace = 'testworld';
        }

        public function test_New()
        {
            $path = 'Resource\App';
            $app = new AppAdapter($this->injector, $this->namespace, $path);
            $uri = "page://self/User/Friend";
            $resourceObject = $app->get($uri);
            $this->assertInstanceOf('testworld\Resource\App\User\Friend', $resourceObject);
        }

        public function test_ToUcwordsUri()
        {
            $path = 'Resource\App';
            $app = new AppAdapter($this->injector, $this->namespace, $path);
            $uri = "page://self/user/friend";
            $resourceObject = $app->get($uri);
            $this->assertInstanceOf('testworld\Resource\App\User\Friend', $resourceObject);
        }
    }

}