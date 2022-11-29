<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Module\EmbedResourceModule;
use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\App\Bird\Birds;
use FakeVendor\Sandbox\Resource\App\Bird\BirdsRel;
use FakeVendor\Sandbox\Resource\App\Bird\Sparrow;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function serialize;

class EmbedInterceptorTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->resource = (new Injector(new EmbedResourceModule(new ResourceModule('FakeVendor\Sandbox')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
    }

    public function testInvoke(): Birds
    {
        $result = $this->resource->uri('app://self/bird/birds')(['id' => 1]);
        assert($result instanceof Birds);
        $profile = $result['bird1'];
        /** @var Request $profile */
        $this->assertInstanceOf(Request::class, $profile);
        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());

        return $result;
    }

    public function testInvokeRelativePath(): BirdsRel
    {
        $result = $this->resource->uri('app://self/bird/birds-rel')(['id' => 1]);
        assert($result instanceof BirdsRel);
        $profile = $result['bird1'];
        /** @var Request $profile */
        $this->assertInstanceOf(Request::class, $profile);
        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());

        return $result;
    }

    /** @depends testInvoke */
    public function testInvokeAnotherLink(ResourceObject $result): ResourceObject
    {
        $profile = $result['bird2'];
        assert($profile instanceof Request);
        $this->assertInstanceOf(Request::class, $profile);
        $this->assertSame('get app://self/bird/sparrow?id=1', $profile->toUriWithMethod());

        return $result;
    }

    /** @depends testInvoke */
    public function testInvokeString(ResourceObject $result): void
    {
        $result->setRenderer(new JsonRenderer());
        $json = $result->toString();
        $this->assertSame('{"bird1":{"name":"chill kun"},"bird2":{"sparrow_id":"1"}}', $json);
    }

    public function testEmbedAnnotation(): Request
    {
        $request = $this->resource->get->uri('app://self/bird/birds')->withQuery(['id' => 1])->request();
        assert($request instanceof Request);
        $this->assertSame('app://self/bird/birds?id=1', $request->toUri());
        $ro = $request();
        $bird1 = $ro['bird1'];
        assert($bird1 instanceof Request);
        $bird2 = $ro['bird2'];
        assert($bird2 instanceof Request);
        $this->assertSame('app://self/bird/canary', $bird1->toUri());
        $this->assertSame('app://self/bird/sparrow?id=1', $bird2->toUri());
        $bird2 = $ro['bird2'];
        assert($bird2 instanceof Request);

        return $bird2;
    }

    /** @depends testEmbedAnnotation */
    public function testEmbedChangeQuery(AbstractRequest $request): void
    {
        $request->withQuery(['id' => 100]);
        $this->assertSame('app://self/bird/sparrow?id=100', $request->toUri());
        $request->addQuery(['option' => 'yes']);
        $this->assertSame('app://self/bird/sparrow?id=100&option=yes', $request->toUri());
    }

    public function testNotFoundSrc(): void
    {
        $this->expectException(EmbedException::class);
        $this->resource->uri('app://self/bird/not-found-bird')(['id' => 1]);
    }

    public function testNotInvalidSrc(): void
    {
        $this->expectException(EmbedException::class);
        $this->resource->uri('app://self/bird/invalid-bird')(['id' => 1]);
    }

    public function testEmbedAnnotationResource(): void
    {
        $request = $this
            ->resource
            ->get
            ->uri('app://self/bird/sparrows')
            ->withQuery(['id_request' => 3, 'id_object' => 5, 'id_eager_request' => 7])
            ->request();
        assert($request instanceof Request);
        $this->assertSame('app://self/bird/sparrows?id_request=3&id_object=5&id_eager_request=7', $request->toUri());
        assert($request instanceof Request);
        $ro = $request();
        $birdRequest = $ro['birdRequest'];
        assert($birdRequest instanceof Request);
        $birdObject = $ro['birdObject'];
        assert($birdObject instanceof ResourceObject);
        $this->assertInstanceOf(Request::class, $birdRequest);
        $this->assertSame('get app://self/bird/sparrow?id=3', $birdRequest->toUriWithMethod());
        $this->assertInstanceOf(Sparrow::class, $birdObject);
        $this->assertSame(serialize($birdObject->body), serialize(['sparrow_id' => 5]));
    }
}
