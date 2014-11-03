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

    class AppTest extends \PHPUnit_Framework_TestCase
    {
        protected $injector;
        protected $namespace;

        protected function setUp()
        {
            parent::setUp();
            $this->injector = new Injector;
            $this->namespace = 'testworld';
        }

        /**
         * @expectedException \BEAR\Resource\Exception\AppNamespace
         */
        public function testNewNamespace()
        {
            new AppAdapter($this->injector, [], '');
        }

        public function testNew()
        {
            $path = 'Resource\App';
            $app = new AppAdapter($this->injector, $this->namespace, $path);
            $uri = "page://self/User/Friend";
            $resourceObject = $app->get($uri);
            $this->assertInstanceOf('testworld\Resource\App\User\Friend', $resourceObject);
        }

        public function testToUcwordsUri()
        {
            $path = 'Resource\App';
            $app = new AppAdapter($this->injector, $this->namespace, $path);
            $uri = "page://self/user/friend";
            $resourceObject = $app->get($uri);
            $this->assertInstanceOf('testworld\Resource\App\User\Friend', $resourceObject);
        }

        public function testIterator()
        {
            $injector = new Injector;
            $resourceDir = $_ENV['TEST_DIR'] . '/MyVendor';
            $app = new App($injector, 'MyVendor\Sandbox', '', $resourceDir);
            foreach ($app as $meta) {
                $this->assertInstanceOf('BEAR\Resource\Meta', $meta);
            }

            return $app;
        }

        /**
         * @param $app
         *
         * @depends testIterator
         */
        public function testIteratorContents($app)
        {
            foreach ($app as $meta) {
                $this->assertInstanceOf('BEAR\Resource\Meta', $meta);
            }
        }
    }

}
