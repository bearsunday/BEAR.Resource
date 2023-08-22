<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterEnumTypeException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use BEAR\Resource\Module\ResourceModule;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\Page\EnumParam;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function call_user_func_array;

class NamedParameterTest extends TestCase
{
    private NamedParameter $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = new NamedParameter(new NamedParamMetas(new AnnotationReader()), new Injector());
    }

    public function testGetParameters(): void
    {
        $object = new FakeParamResource();
        $namedArgs = ['id' => 1, 'name' => 'koriym'];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testDefaultValue(): void
    {
        $object = new FakeParamResource();
        $namedArgs = ['id' => 1];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testParameterException(): void
    {
        $this->expectException(ParameterException::class);
        $object = new FakeParamResource();
        $namedArgs = [];
        $this->params->getParameters([$object, 'onGet'], $namedArgs);
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
        $object = new FakeParamResource();
        $expected = [
            'cookie_val',
            'env_val',
            'post_val',
            'get_val',
            'server_val',
        ];
        $args = $this->params->getParameters([$object, 'onPost'], []);
        $this->assertSame($expected, $args);
    }

    public function testParameterWebContextNotExits(): void
    {
        $this->expectException(ParameterException::class);
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $object = new FakeParamResource();
        $this->params->getParameters([$object, 'onPut'], ['cookie' => 1]); // should be ignored
    }

    public function testParameterWebContextDefault(): void
    {
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $object = new FakeParamResource();
        $expected = [
            1,
            'default',
        ];
        $args = $this->params->getParameters([$object, 'onDelete'], ['a' => 1]);
        $this->assertSame($expected, $args);
    }

    public function testParameterWebContexRequiredNotGiven(): void
    {
        $this->expectException(ParameterException::class);
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose([]);
        $object = new FakeParamResource();
        $args = $this->params->getParameters([$object, 'onDelete'], []);
    }

    public function testCameCaseParam(): void
    {
        $object = new FakeCamelCaseParamResource();
        $namedArgs = ['user_id' => 'koriym', 'user_role' => 'lead'];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame(['koriym', 'lead'], $args);
        $ro = call_user_func_array([$object, 'onGet'], $args);
        assert($ro instanceof ResourceObject);
        $this->assertSame(['userId' => 'koriym', 'userRole' => 'lead'], (array) $ro->body);
    }

    /** @requires PHP >= 8.1 */
    public function testEnumParam(): void
    {
        $ro = new EnumParam();

        $params = ['stringBacked' => 'foo', 'intBacked' => '1'];
        $args = $this->params->getParameters([$ro, 'onGet'], $params);

        $this->assertSame([FakeStringBacked::FOO, FakeIntBacked::FOO, null], $args);
    }

    /** @requires PHP >= 8.1 */
    public function testEnumInvlidType(): void
    {
        $this->expectException(ParameterEnumTypeException::class);
        $this->expectExceptionMessage('intBacked');
        $ro = new EnumParam();
        $params = ['stringBacked' => 'foo', 'intBacked' => new DateTime()];
        $this->params->getParameters([$ro, 'onGet'], $params);
    }

    /** @requires PHP >= 8.1 */
    public function testWithResourceClient(): void
    {
        $resource = (new Injector(new ResourceModule('FakeVendor\Sandbox')))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $params = ['stringBacked' => 'foo', 'intBacked' => 1];
        $body = $resource->get('page://self/enum-param', $params)->body;
        $this->assertSame(['stringBacked' => 'foo', 'intBacked' => 1], $body);
    }

    /** @requires PHP >= 8.1 */
    public function testEnumParamWithResourceClient(): void
    {
        $this->expectException(ParameterInvalidEnumException::class);
        $resource = (new Injector(new ResourceModule('FakeVendor\Sandbox')))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $params = ['stringBacked' => '__not_enum_value__', 'intBacked' => '1'];
        $resource->get('page://self/enum-param', $params);
    }

    /** @requires PHP >= 8.1 */
    public function testNotBackedEnumParamWithResourceClient(): void
    {
        $this->expectException(NotBackedEnumException::class);
        $resource = (new Injector(new ResourceModule('FakeVendor\Sandbox')))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $params = ['notBacked' => 'foo'];
        $resource->put('page://self/enum-param', $params);
    }
}
