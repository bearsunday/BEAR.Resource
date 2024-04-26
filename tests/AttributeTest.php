<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use FakeVendor\News\Resource\App\News;
use FakeVendor\News\Resource\App\WebParam;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;

class AttributeTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        parent::setUp();

        $injector = new Injector(new ResourceModule('FakeVendor\News'), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testNewInstance(): News
    {
        $instance = $this->resource->newInstance('app://self/news');
        $this->assertInstanceOf(News::class, $instance);
        assert($instance instanceof News);

        return $instance;
    }

    /** @depends testNewInstance */
    public function testEmbeded(News $news): void
    {
        $ro = $news->onGet('2021/7/23');
        $this->assertInstanceOf(Request::class, $ro->body['weather']);
    }

    /**
     * @depends testNewInstance
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

    public function testResourceParam(): void
    {
        $ro = $this->resource->get->uri('app://self/greeting')->eager->request();
        $this->assertSame('kumakun', $ro->body['nickname']);
    }

    public function testResourceParamInUriTemplate(): void
    {
        $ro = $this->resource->post->uri('app://self/greeting')->withQuery(['name' => 'BEAR'])->eager->request();
        $this->assertSame('login:BEAR', $ro->body['id']);
    }

    public function testWebParam(): void
    {
        $fakeGlobals = [
            '_COOKIE' => ['c' => 'cookie_val'],
            '_ENV' => ['e' => 'env_val'],
            '_POST' => ['f' => 'post_val'],
            '_GET' => ['q' => 'get_val'],
            '_SERVER' => ['s' => 'server_val'],
        ];
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose($fakeGlobals);
        $ro = $this->resource->get('app://self/web-param');
        assert($ro instanceof WebParam);
        $this->assertSame('cookie_val', $ro->cookie);
        $this->assertSame('env_val', $ro->env);
        $this->assertSame('post_val', $ro->form);
        $this->assertSame('get_val', $ro->query);
        $this->assertSame('server_val', $ro->server);
    }
}
