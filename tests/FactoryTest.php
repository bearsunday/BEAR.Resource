<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Exception\SchemeException;
use BEAR\Resource\Exception\UriException;
use FakeVendor\Sandbox\Resource\App\Factory\News;
use FakeVendor\Sandbox\Resource\Page\HelloWorld;
use FakeVendor\Sandbox\Resource\Page\Index as IndexPage;
use FakeVendor\Sandbox\Resource\Page\News as PageNews;
use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;
use Ray\Di\Injector;

use function get_class;

class FactoryTest extends TestCase
{
    /** @var Factory */
    protected $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $injector = new Injector();
        $scheme = (new SchemeCollection())
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('prov')->host('self')->toAdapter(new FakeProv())
            ->scheme('nop')->host('self')->toAdapter(new FakeNop());
        $this->factory = new Factory($scheme, new UriFactory());
    }

    public function testNewInstanceNop(): void
    {
        $instance = $this->factory->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf(FakeNopResource::class, $instance);
    }

    public function testNewInstanceWithProvider(): void
    {
        $instance = $this->factory->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf(ResourceObject::class, $instance);
    }

    public function testNewInstanceScheme(): void
    {
        $this->expectException(SchemeException::class);
        $this->factory->newInstance('bad://self/news');
    }

    public function testNewInstanceSchemes(): void
    {
        $this->expectException(SchemeException::class);
        $this->factory->newInstance('app://invalid_host/news');
    }

    public function testNewInstanceApp(): void
    {
        $instance = $this->factory->newInstance('app://self/factory/news');
        $this->assertInstanceOf(News::class, $instance, get_class($instance));
    }

    public function testNewInstancePage(): void
    {
        $instance = $this->factory->newInstance('page://self/news');
        $this->assertInstanceOf(PageNews::class, $instance);
    }

    public function testInvalidUri(): void
    {
        $this->expectException(UriException::class);
        $this->factory->newInstance('invalid_uri');
    }

    public function testInvalidObjectUri(): void
    {
        $this->expectException(UriException::class);
        $this->factory->newInstance('');
    }

    public function testResourceNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->factory->newInstance('page://self/not_found_XXXX');
    }

    public function testUnbound(): void
    {
        $this->expectException(Unbound::class);
        $instance = $this->factory->newInstance('page://self/unbound');
    }

    public function testIndexSuffix(): void
    {
        $instance = $this->factory->newInstance('page://self/');
        $this->assertInstanceOf(IndexPage::class, $instance);
    }

    public function testDash(): void
    {
        $instance = $this->factory->newInstance('page://self/hello-world');
        $this->assertInstanceOf(HelloWorld::class, $instance);
    }
}
