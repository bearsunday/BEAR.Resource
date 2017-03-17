<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\Bird\Birds;
use FakeVendor\Sandbox\Resource\App\Bird\BirdsRel;
use FakeVendor\Sandbox\Resource\App\Bird\InvalidBird;
use FakeVendor\Sandbox\Resource\App\Bird\NotFoundBird;
use FakeVendor\Sandbox\Resource\App\Bird\Sparrow;
use Ray\Aop\Arguments;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Injector;

class EmbedInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    /**
     * @var EmbedInterceptor
     */
    private $embedInterceptor;

    protected function setUp()
    {
        $this->resource = (new Injector(new ResourceModule('FakeVendor\Sandbox')))->getInstance(ResourceInterface::class);
        $this->embedInterceptor = new EmbedInterceptor($this->resource, new AnnotationReader);
    }

    public function testInvoke()
    {
        $fake = new Birds;
        $fake->uri = new Uri('app://self/birds');
        $invocation = new ReflectiveMethodInvocation(
            $fake,
            new \ReflectionMethod($fake, 'onGet'),
            new Arguments(['id' => 1]),
            [$this->embedInterceptor]
        );
        $result = $invocation->proceed();
        $profile = $result['bird1'];
        /* @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());

        return $result;
    }

    public function testInvokeRelativePath()
    {
        $fake = new BirdsRel;
        $fake->uri = new Uri('app://self/birds_rel');
        $invocation = new ReflectiveMethodInvocation(
            $fake,
            new \ReflectionMethod($fake, 'onGet'),
            new Arguments(['id' => 1]),
            [$this->embedInterceptor]
        );
        $result = $invocation->proceed();
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
        $json = (string) $result;
        $this->assertSame('{"bird1":{"name":"chill kun"},"bird2":{"sparrow_id":"1"}}', $json);
    }

    public function testEmbedAnnotation()
    {
        $request = $this->resource->get->uri('app://self/bird/birds')->withQuery(['id' => 1])->request();
        /* @var $request Request */
        $this->assertSame('app://self/bird/birds?id=1', $request->toUri());
        $resourceObject = $request();
        $bird1 = $resourceObject['bird1'];
        $bird2 = $resourceObject['bird2'];
        /* @var $bird1 Request */
        /* @var $bird2 Request */
        $this->assertSame('app://self/bird/canary', $bird1->toUri());
        $this->assertSame('app://self/bird/sparrow?id=1', $bird2->toUri());

        return $resourceObject['bird2'];
    }

    /**
     * @param AbstractRequest $request
     *
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
        $this->setExpectedException(EmbedException::class);
        $mock = new NotFoundBird;
        $invocation = new ReflectiveMethodInvocation($mock, new \ReflectionMethod($mock, 'onGet'), new Arguments(['id' => 1]), [$this->embedInterceptor]);
        $invocation->proceed();
    }

    public function testNotInvalidSrc()
    {
        $this->setExpectedException(EmbedException::class);
        $mock = new InvalidBird;
        $invocation = new ReflectiveMethodInvocation($mock, new \ReflectionMethod($mock, 'onGet'), new Arguments(['id' => 1]), [$this->embedInterceptor]);
        $invocation->proceed();
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
        $resourceObject = $request();
        /** @var $birdRequest Request */
        $birdRequest = $resourceObject['birdRequest'];
        $birdObject = $resourceObject['birdObject'];
        $this->assertInstanceOf(Request::class, $birdRequest);
        $this->assertSame('get app://self/bird/sparrow?id=3', $birdRequest->toUriWithMethod());
        $this->assertInstanceOf(Sparrow::class, $birdObject);
        $this->assertSame(serialize($birdObject->body), serialize(['sparrow_id' => 5]));
    }
}
