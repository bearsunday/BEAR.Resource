<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Module\EmbedResourceModule;
use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\App\Bird\Sparrow;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class EmbedInterceptorTest extends TestCase
{
    /**
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    /**
     * @var EmbedInterceptor
     */
    private $embedInterceptor;

    protected function setUp() : void
    {
        $this->resource = (new Injector(new EmbedResourceModule(new ResourceModule('FakeVendor\Sandbox')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
    }

    public function testInvoke()
    {
        $result = $this->resource->uri('app://self/bird/birds')(['id' => 1]);
        $profile = $result['bird1'];
        /* @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());

        return $result;
    }

    public function testInvokeRelativePath()
    {
        $result = $this->resource->uri('app://self/bird/birds-rel')(['id' => 1]);
        $profile = $result['bird1'];
        /* @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());

        return $result;
    }

    /**
     * @depends testInvoke
     */
    public function testInvokeAnotherLink(ResourceObject $result)
    {
        $profile = $result['bird2'];
        /* @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/bird/sparrow?id=1', $profile->toUriWithMethod());

        return $result;
    }

    /**
     * @depends testInvoke
     */
    public function testInvokeString(ResourceObject $result)
    {
        $result->setRenderer(new JsonRenderer);
        $json = $result->toString();
        $this->assertSame('{"bird1":{"name":"chill kun"},"bird2":{"sparrow_id":"1"}}', $json);
    }

    public function testEmbedAnnotation()
    {
        $request = $this->resource->get->uri('app://self/bird/birds')->withQuery(['id' => 1])->request();
        /* @var $request Request */
        $this->assertSame('app://self/bird/birds?id=1', $request->toUri());
        $ro = $request();
        $bird1 = $ro['bird1'];
        $bird2 = $ro['bird2'];
        /* @var $bird1 Request */
        /* @var $bird2 Request */
        $this->assertSame('app://self/bird/canary', $bird1->toUri());
        $this->assertSame('app://self/bird/sparrow?id=1', $bird2->toUri());

        return $ro['bird2'];
    }

    /**
     * @depends testEmbedAnnotation
     */
    public function testEmbedChangeQuery(AbstractRequest $request)
    {
        $request->withQuery(['id' => 100]);
        $this->assertSame('app://self/bird/sparrow?id=100', $request->toUri());
        $request->addQuery(['option' => 'yes']);
        $this->assertSame('app://self/bird/sparrow?id=100&option=yes', $request->toUri());
    }

    public function testNotFoundSrc()
    {
        $this->expectException(EmbedException::class);
        $this->resource->uri('app://self/bird/not-found-bird')(['id' => 1]);
    }

    public function testNotInvalidSrc()
    {
        $this->expectException(EmbedException::class);
        $this->resource->uri('app://self/bird/invalid-bird')(['id' => 1]);
    }

    public function testEmbedAnnotationResource()
    {
        $request = $this
            ->resource
            ->get
            ->uri('app://self/bird/sparrows')
            ->withQuery(['id_request' => 3, 'id_object' => 5, 'id_eager_request' => 7])
            ->request();

        $this->assertSame('app://self/bird/sparrows?id_request=3&id_object=5&id_eager_request=7', $request->toUri());

        /* @var $request Request */
        $ro = $request();
        /* @var $birdRequest Request */
        $birdRequest = $ro['birdRequest'];
        $birdObject = $ro['birdObject'];
        $this->assertInstanceOf(Request::class, $birdRequest);
        $this->assertSame('get app://self/bird/sparrow?id=3', $birdRequest->toUriWithMethod());
        $this->assertInstanceOf(Sparrow::class, $birdObject);
        $this->assertSame(serialize($birdObject->body), serialize(['sparrow_id' => 5]));
    }
}
