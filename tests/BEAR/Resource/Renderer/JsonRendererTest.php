<?php

namespace BEAR\Resource\Renderer;

use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use BEAR\Resource\Request;
use BEAR\Resource\Linker;
use BEAR\Resource\Invoker;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\NamedParams;
use BEAR\Resource\SignalParam;
use BEAR\Resource\Param;
use Doctrine\Common\Annotations\AnnotationReader;
use BEAR\Resource\Logger;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use BEAR\Resource\Renderer\JsonRenderer;

class RequestSample
{
    public function __toString()
    {
        return __CLASS__;
    }
}

/**
 * Test class for JsonRenderer.
 */
class JsonRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testResource;

    protected function setUp()
    {
        parent::setUp();
        $invoker = new Invoker(
            new Linker(new AnnotationReader),
            new NamedParams(
                new SignalParam(
                    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
                    new Param
                )
            ),
            new Logger
        );
        $request = new Request($invoker);
        $request->method = 'get';
        $this->testResource = new Ok;
        $request->ro = $this->testResource;
        $request->ro->uri = 'test://self/path/to/resource';

        $this->testResource['one'] = 1;
        $this->testResource['two'] = $request;
        $this->testResource->setRenderer(new JsonRenderer);
    }

    public function testRender()
    {
        // json render
        $result = (string)$this->testResource;
        $data = json_decode($result, true);
        $expected = array(
            'one' => 1,
            'two' => array(
                'code' => 200,
                'headers' => array(),
                'body' => array(
                    'one' => 1,
                    'two' => null,
                ),
                'uri' => 'test://self/path/to/resource',
                'view' => null,
                'links' => []
            ),
        );
        $this->assertSame($expected, $data);
    }

}

final class Ok extends AbstractObject
{
    /**
     * Code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * Body
     *
     * @var mixed
     */
    public $body = '';

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get
     *
     * @return $this
     */
    public function onGet()
    {
        return $this;
    }
}
