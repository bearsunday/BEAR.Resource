<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\News\Resource\App\Event;
use FakeVendor\News\Resource\App\News;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\App\Href\Hasembed;
use FakeVendor\Sandbox\Resource\App\Href\Origin;
use FakeVendor\Sandbox\Resource\App\Href\Target;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\NullModule;

use function assert;
use function var_dump;
use function var_export;

class AttributeTest extends TestCase
{
    /** @var ResourceInterface */
    private $resource;

    protected function setUp() : void
    {
        parent::setUp();
        $injector = new Injector(new ResourceModule('FakeVendor\News'), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testNewInstance(): News
    {
        $instance = $this->resource->newInstance('app://self/news');
        $this->assertInstanceOf(News::class, $instance);

        return $instance;
    }

    /**
     * @depends testNewInstance
     */
    public function testEmbeded(News $news): void
    {
        $ro = $news->onGet('2021/7/23');
        $this->assertInstanceOf(Request::class, $ro->body['weather']);
    }

    /**
     * @depends testNewInstance
     *
     * @see ResourceTest::testLinkSelf()
     */
    public function testLink(News $news): void
    {
        $request = $this->resource->get->uri('app://self/news')->withQuery(['date' => '2021/7/23'])->linkSelf('event')->request();
        assert($request instanceof Request);
        $this->assertSame('event', $request->links[0]->key);
        $this->assertSame(LinkType::SELF_LINK, $request->links[0]->type);
        $ro = $request();
        $this->assertSame(200, $ro->code);
        $this->assertArrayHasKey('event', $ro->body);
        $this->assertSame('2021/7/23', $ro->body['event']);
    }
}