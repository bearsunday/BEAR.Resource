<?php

namespace ExceptionTest {

    use BEAR\Resource\AbstractRequest;
    use BEAR\Resource\ExceptionHandlerInterface;

    class MyHandler implements ExceptionHandlerInterface
    {
        /**
         * {@inheritdoc}
         */
        public function handle(\Exception $e, AbstractRequest $request)
        {
            return  'handled:' . $request->toUri();
        }
    }
}

namespace ExceptionTest\Resource\Page {

    use BEAR\Resource\ResourceInterface;
    use BEAR\Resource\ResourceObject;
    use ExceptionTest\MyHandler;
    use Ray\Di\Di\Inject;

    class Index extends ResourceObject
    {
        private $resource;

        /**
         * @Inject
         */
        public function __construct(ResourceInterface $resource)
        {
            $this->resource = $resource;
            $this->resource->setExceptionHandler(new MyHandler());
        }

        public function onGet()
        {
            throw new \RuntimeException;
        }
    }
}

namespace BEAR\Resource {

    use BEAR\Resource\Module\ResourceModule;
    use Ray\Di\Injector;
    use Ray\Di\Module\InjectorModule;

    class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
    {
        protected $resource;

        public function setUp()
        {
            $this->resource = (new Injector(new ResourceModule('ExceptionTest')))->getInstance('BEAR\Resource\ResourceInterface');
        }

        public function testException()
        {
            $result = $this->resource->get->uri('page://self/index')->eager->request();
            $this->assertSame('handled:page://self/index', $result->body);
        }
    }
}
