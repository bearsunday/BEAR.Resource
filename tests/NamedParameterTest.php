<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Injector;

class NamedParameterTest extends \PHPUnit_Framework_TestCase
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

    public function testParameterException()
    {
        $this->setExpectedException(ParameterException::class, null, Code::BAD_REQUEST);
        $object = new FakeParamResource;
        $namedArgs = [];
        $this->params->getParameters([$object, 'onGet'], $namedArgs);
    }
}
