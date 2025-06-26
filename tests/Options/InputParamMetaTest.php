<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Annotation\Input;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InputParamMetaTest extends TestCase
{
    private InputParamMeta $inputParamMeta;

    protected function setUp(): void
    {
        $this->inputParamMeta = new InputParamMeta();
    }

    public function testNoInputParameters(): void
    {
        $method = new ReflectionMethod(TestNoInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertSame([], $result);
    }

    public function testSingleInputParameter(): void
    {
        $method = new ReflectionMethod(TestSingleInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertArrayHasKey('name', $result['parameters']);
        $this->assertArrayHasKey('age', $result['parameters']);

        $this->assertSame('string', $result['parameters']['name']['type']);
        $this->assertSame('integer', $result['parameters']['age']['type']);
        $this->assertSame('user', $result['parameters']['name']['group']);
        $this->assertSame('user', $result['parameters']['age']['group']);
    }

    public function testInputParameterWithDefaults(): void
    {
        $method = new ReflectionMethod(TestInputWithDefaultsResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertArrayHasKey('page', $result['parameters']);
        $this->assertArrayHasKey('limit', $result['parameters']);

        $this->assertSame('1', $result['parameters']['page']['default']);
        $this->assertSame('20', $result['parameters']['limit']['default']);

        $this->assertArrayNotHasKey('required', $result);
    }

    public function testNestedInputParameters(): void
    {
        $method = new ReflectionMethod(TestNestedInputResource::class, 'onPost');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        // Parent level parameters
        $this->assertArrayHasKey('name', $result['parameters']);
        $this->assertArrayHasKey('age', $result['parameters']);
        // Nested parameters
        $this->assertArrayHasKey('city', $result['parameters']);
        $this->assertArrayHasKey('street', $result['parameters']);

        $this->assertSame('user', $result['parameters']['name']['group']);
        $this->assertSame('address', $result['parameters']['city']['group']);
    }

    public function testRequiredParameters(): void
    {
        $method = new ReflectionMethod(TestRequiredInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('required', $result);
        $this->assertContains('query', $result['required']);
        $this->assertNotContains('category', $result['required']);
    }
}

// Test resource classes
class TestNoInputResource
{
    public function onGet(string $id): void
    {
    }
}

class TestSingleInputResource
{
    /** @param TestUser $user User information */
    public function onGet(#[Input]
    TestUser $user,): void
    {
    }
}

class TestInputWithDefaultsResource
{
    public function onGet(#[Input]
    TestPager $pager,): void
    {
    }
}

class TestNestedInputResource
{
    public function onPost(#[Input]
    TestUserWithAddress $user,): void
    {
    }
}

class TestRequiredInputResource
{
    public function onGet(#[Input]
    TestSearchCriteria $criteria,): void
    {
    }
}

// Test input classes
final class TestUser
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {
    }
}

final class TestPager
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
    }
}

final class TestAddress
{
    /**
     * @param string $city   City name
     * @param string $street Street address
     */
    public function __construct(
        public readonly string $city,
        public readonly string $street,
    ) {
    }
}

final class TestUserWithAddress
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        #[Input]
        public readonly TestAddress $address,
    ) {
    }
}

final class TestSearchCriteria
{
    /**
     * @param string      $query    Search query
     * @param string|null $category Category filter
     */
    public function __construct(
        public readonly string $query,
        public readonly string|null $category = null,
    ) {
    }
}
