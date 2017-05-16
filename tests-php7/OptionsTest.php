<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\DocPhp7;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        $this->invoker = new Invoker(new NamedParameter(new ArrayCache, new VoidParamHandler), new OptionsRenderer(new AnnotationReader));
    }

    public function testOptionsMethod()
    {
        $request = new Request($this->invoker, new DocPhp7, Request::OPTIONS);
        $response = $this->invoker->invoke($request);
        $actual = $response->headers['allow'];
        $expected = 'GET';
        $this->assertSame($actual, $expected);

        return $response;
    }

    /**
     * @depends testOptionsMethod
     */
    public function testOptionsMethodBody(ResourceObject $ro)
    {
        $actual = $ro->view;
        $expected = '{
    "GET": {
        "parameters": {
            "id": {
                "type": "integer"
            },
            "name": {
                "type": "string"
            },
            "sw": {
                "type": "bool"
            },
            "arr": {
                "type": "array"
            }
        },
        "required": [
            "id",
            "name",
            "sw",
            "arr"
        ]
    }
}
';
        $this->assertSame($expected, $actual);
    }
}
