<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\News\Resource\App\AttrWebContext;
use Koriym\Attributes\AttributeReader;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\ServiceLocator\ServiceLocator;

use function call_user_func_array;

class AttrNamedParameterTest extends TestCase
{
    private NamedParameter $params;

    /** @var FakeAttrContext */
    private $ro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->params = new NamedParameter(new NamedParamMetas(new AttributeReader()), new Injector());
        $this->ro = new AttrWebContext();
    }

    public function testGetParameters(): void
    {
        $namedArgs = ['id' => 1, 'name' => 'koriym'];
        $args = $this->params->getParameters([$this->ro, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testDefaultValue(): void
    {
        $namedArgs = ['id' => 1];
        $args = $this->params->getParameters([$this->ro, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testParameterException(): void
    {
        $this->expectException(ParameterException::class);
        $namedArgs = [];
        $this->params->getParameters([$this->ro, 'onGet'], $namedArgs);
    }

    public function testParameterWebContext(): void
    {
        $fakeGlobals = [
            '_COOKIE' => ['c' => 'cookie_val'],
            '_ENV' => ['e' => 'env_val'],
            '_POST' => ['f' => 'post_val'],
            '_GET' => ['q' => 'get_val'],
            '_SERVER' => ['s' => 'server_val'],
        ];
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose($fakeGlobals);
        $expected = [
            'cookie_val',
            'env_val',
            'post_val',
            'get_val',
            'server_val',
        ];
        $args = $this->params->getParameters([$this->ro, 'onPost'], []);
        $this->assertSame($expected, $args);
    }

    public function testParameterWebContextNotExits(): void
    {
        $this->expectException(ParameterException::class);
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $this->params->getParameters([$this->ro, 'onPut'], ['cookie' => 1]); // should be ignored
    }

    public function testParameterWebContextDefault(): void
    {
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $expected = [
            1,
            'default',
        ];
        $args = $this->params->getParameters([$this->ro, 'onDelete'], ['a' => 1]);
        $this->assertSame($expected, $args);
    }

    public function testParameterWebContexRequiredNotGiven(): void
    {
        $this->expectException(ParameterException::class);
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $this->ro = new FakeParamResource();
        $this->params->getParameters([$this->ro, 'onDelete'], []);
    }

}
