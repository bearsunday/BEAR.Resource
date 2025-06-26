<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\OptionsMethods;
use BEAR\Resource\ResourceObject;
use PHPUnit\Framework\TestCase;

class OptionsMethodsInputTest extends TestCase
{
    private OptionsMethods $optionsMethods;

    protected function setUp(): void
    {
        $this->optionsMethods = new OptionsMethods(new InputParamMeta(), '');
    }

    public function testOptionsWithInputAttributes(): void
    {
        $resource = new TestInputResource();
        $result = ($this->optionsMethods)($resource, 'Get');

        $this->assertArrayHasKey('request', $result);
        $this->assertArrayHasKey('parameters', $result['request']);

        $parameters = $result['request']['parameters'];

        // Check that Input attribute parameters are included
        $this->assertArrayHasKey('name', $parameters);
        $this->assertArrayHasKey('age', $parameters);
        $this->assertArrayHasKey('format', $parameters); // regular parameter

        // Check Input parameter metadata
        $this->assertSame('string', $parameters['name']['type']);
        $this->assertSame('integer', $parameters['age']['type']);
        $this->assertSame('user', $parameters['name']['group']);
        $this->assertSame('user', $parameters['age']['group']);

        // Check regular parameter
        $this->assertSame('string', $parameters['format']['type']);
        $this->assertSame('json', $parameters['format']['default']);
        $this->assertArrayNotHasKey('group', $parameters['format']);
    }

    public function testOptionsWithNestedInput(): void
    {
        $resource = new TestNestedInputResourceIntegration();
        $result = ($this->optionsMethods)($resource, 'Post');

        $this->assertArrayHasKey('request', $result);
        $parameters = $result['request']['parameters'];

        // Check parent parameters
        $this->assertArrayHasKey('firstName', $parameters);
        $this->assertArrayHasKey('lastName', $parameters);
        $this->assertSame('user', $parameters['firstName']['group']);

        // Check nested parameters
        $this->assertArrayHasKey('city', $parameters);
        $this->assertArrayHasKey('zipCode', $parameters);
        $this->assertSame('address', $parameters['city']['group']);
    }

    public function testOptionsWithRequiredParameters(): void
    {
        $resource = new TestRequiredInputResourceIntegration();
        $result = ($this->optionsMethods)($resource, 'Get');

        $this->assertArrayHasKey('request', $result);
        $this->assertArrayHasKey('required', $result['request']);

        $required = $result['request']['required'];
        $this->assertContains('query', $required);
        $this->assertNotContains('category', $required); // has default
    }
}

// Test resources
class TestInputResource extends ResourceObject
{
    /**
     * Get user information
     *
     * @param TestUserData $user   User data
     * @param string       $format Response format
     */
    public function onGet(
        #[Input]
        TestUserData $user,
        string $format = 'json',
    ): static {
        return $this;
    }
}

class TestNestedInputResourceIntegration extends ResourceObject
{
    public function onPost(#[Input]
    TestUserWithAddressData $user,): static
    {
        return $this;
    }
}

class TestRequiredInputResourceIntegration extends ResourceObject
{
    public function onGet(#[Input]
    TestSearchData $criteria,): static
    {
        return $this;
    }
}

// Test data classes
final class TestUserData
{
    /**
     * @param string $name User name
     * @param int    $age  User age
     */
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {
    }
}

final class TestAddressData
{
    /**
     * @param string $city    City name
     * @param string $zipCode ZIP code
     */
    public function __construct(
        public readonly string $city,
        public readonly string $zipCode,
    ) {
    }
}

final class TestUserWithAddressData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        #[Input]
        public readonly TestAddressData $address,
    ) {
    }
}

final class TestSearchData
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
