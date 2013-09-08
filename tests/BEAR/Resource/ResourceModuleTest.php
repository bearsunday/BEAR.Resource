<?php

namespace Sandbox\Resource\Page {

    use BEAR\Resource\AbstractObject;

    class Index extends AbstractObject
    {
        public $name;

        public function onGet($name)
        {
            $this->name = $name;
            return $this;
        }
    }
}

namespace Another\Resource\Page {

    use BEAR\Resource\AbstractObject;

    class Index extends AbstractObject
    {
        public function onGet()
        {
            return $this;
        }
    }
}


namespace BEAR\Resource {

    use BEAR\Resource\Module\ResourceModule;
    use BEAR\Resource\Adapter\Http as HttpAdapter;
    use Doctrine\Common\Annotations\AnnotationReader;
    use Ray\Di\AbstractModule;
    use Ray\Di\Injector;
    use Ray\Di\InjectorInterface;
    use Ray\Di\Di\Inject;
    use Ray\Di\Module\InjectorModule;

    class AnotherAppModule extends AbstractModule
    {
        protected function configure()
        {
            $this->bind()->annotatedWith('app_name')->toInstance('Another');
            $this->install(new ResourceModule);
        }
    }

    class SchemeModifyModule extends AbstractModule
    {
        protected function configure()
        {
            $this->install(new InjectorModule(new ResourceModule));
            $this->requestInjection(__NAMESPACE__ . '\Modify')->modify();
        }
    }

    class Modify
    {
        private $schemeCollection;

        /**
         * @param SchemeCollectionInterface $schemeCollection
         * @Inject
         */
        public function __construct(SchemeCollectionInterface $schemeCollection)
        {
            $this->schemeCollection = $schemeCollection;
        }

        public function modify()
        {
            $this->schemeCollection->scheme('https')->host('*')->toAdapter(new HttpAdapter);
        }
    }

    class MyApp
    {
        public $injector;
        public $resource;

        /**
         * @Inject
         */
        public function __construct(
            InjectorInterface $injector,
            ResourceInterface $resource
        ) {
            $this->injector = $injector;
            $this->resource = $resource;
        }
    }


    class ResourceModuleTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @var \Ray\Di\AbstractModule
         */
        private $module;

        protected function setUp()
        {
            AnnotationReader::addGlobalIgnoredName('noinspection');
            $this->module = new InjectorModule(new ResourceModule);
        }

        public function testResourceModule()
        {
            $resource = Injector::create([$this->module])->getInstance('BEAR\Resource\ResourceInterface');
            $this->assertInstanceOf('BEAR\Resource\Resource', $resource);

            return $resource;
        }

        public function testResourceModuleCreateResourceObject()
        {
            $resource = Injector::create([$this->module])->getInstance('BEAR\Resource\ResourceInterface');
            $page = $resource->get->uri('page://self/index')->withQuery(['name' => 'koriym'])->eager->request();
            $this->assertInstanceOf('Sandbox\Resource\Page\Index', $page);

            return $page;
        }

        /**
         * @depends testResourceModuleCreateResourceObject
         */
        public function testResourceQuery($page)
        {
            $this->assertSame('koriym', $page->name);
        }

        public function testCreateResourceObjectOfAnotherApplication()
        {
            $resource = Injector::create([new InjectorModule(new AnotherAppModule)])->getInstance('BEAR\Resource\ResourceInterface');
            $page = $resource->get->uri('page://self/index')->eager->request();
            $this->assertInstanceOf('Another\Resource\Page\Index', $page);
        }

        public function testApp()
        {
            $app = Injector::create([new InjectorModule(new SchemeModifyModule)])->getInstance('BEAR\Resource\MyApp');
            /** @var $app \BEAR\Resource\MyApp */
            $page = $app->resource->get->uri('page://self/index')->withQuery(['name' => 'koriym'])->eager->request();
            $this->assertInstanceOf('Sandbox\Resource\Page\Index', $page);

            return $app;
        }

        /**
         * @depends testApp
         */
        public function TODO_TestAppDependencyModification($app)
        {
            $app->resource->get->uri('https://www.example.com')->eager->request();
        }
    }
}
