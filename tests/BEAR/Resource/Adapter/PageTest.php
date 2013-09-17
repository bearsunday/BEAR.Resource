<?php

namespace testworld\Resource\App\User {

    class Friend
    {
    }
}

namespace BEAR\Resource\Adapter {

    use Ray\Di\Definition;
    use Ray\Di\Injector;
    use BEAR\Resource\Adapter\App as AppAdapter;
    use Doctrine\Common\Annotations\AnnotationReader as Reader;

    class AppTest extends \PHPUnit_Framework_TestCase
    {
        protected $injector;
        protected $namespace;

        protected function setUp()
        {
            parent::setUp();

            $this->injector = Injector::create([]);
            $this->namespace = 'testworld';
        }

        /**
         * @expectedException \RuntimeException
         */
        public function test_NewInvalidNamespace()
        {
            new AppAdapter($this->injector, [], '');
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
