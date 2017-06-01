<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class NamedParameterTest extends TestCase
{
    /**
     * @var NamedParameter
     */
    private $params;

    public function setUp()
    {
        parent::setUp();
        $this->params = new NamedParameter(new ArrayCache, new AnnotationReader, new Injector);
    }

    public function testGetParameters()
    {
        $object = new FakeParamResource;
        $namedArgs = ['id' => 1, 'name' => 'koriym'];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testDefaultValue()
    {
        $object = new FakeParamResource;
        $namedArgs = ['id' => 1];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ParameterException
     */
    public function testParameterException()
    {
        $object = new FakeParamResource;
        $namedArgs = [];
        $this->params->getParameters([$object, 'onGet'], $namedArgs);
    }

    public function testParameterWebContext()
    {
        $fakeGlobals = [
            '_COOKIE' => ['c' => 'cookie_val'],
            '_ENV' => ['e' => 'env_val'],
            '_POST' => ['f' => 'post_val'],
            '_GET' => ['q' => 'get_val'],
            '_SERVER' => ['s' => 'server_val']
        ];
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose($fakeGlobals);
        $object = new FakeParamResource;
        $expected = [
            'cookie_val',
            'env_val',
            'post_val',
            'get_val',
            'server_val'
        ];
        $args = $this->params->getParameters([$object, 'onPost'], []);
        $this->assertSame($expected, $args);
    }
}
