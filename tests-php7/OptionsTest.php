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
use Ray\Di\Injector;

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
        $this->invoker = new Invoker(new NamedParameter(new ArrayCache, new AnnotationReader, new Injector), new OptionsRenderer(new AnnotationReader));
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
                "type": "integer",
                "description": "Id"
            },
            "name": {
                "type": "string",
                "description": "Name"
            },
            "sw": {
                "type": "bool",
                "description": "Swithc"
            },
            "arr": {
                "type": "array"
            },
            "defaultNull": {
                "type": "string",
                "description": "DefaultNull"
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
