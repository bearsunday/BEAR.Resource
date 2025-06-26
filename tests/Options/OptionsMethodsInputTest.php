<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Fake\InputParam\Integration\FakeInputResource;
use BEAR\Resource\Fake\InputParam\Integration\FakeNestedInputResourceIntegration;
use BEAR\Resource\Fake\InputParam\Integration\FakeRequiredInputResourceIntegration;
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
        $resource = new FakeInputResource();
        $result = ($this->optionsMethods)($resource, 'Get');

        $this->assertArrayHasKey('request', $result);
        $this->assertIsArray($result['request']);
        $this->assertArrayHasKey('parameters', $result['request']);

        $parameters = $result['request']['parameters'];
        $this->assertIsArray($parameters);

        // Check that Input attribute parameters are included
        $this->assertArrayHasKey('name', $parameters);
        $this->assertArrayHasKey('age', $parameters);
        $this->assertArrayHasKey('format', $parameters); // regular parameter

        // Check Input parameter metadata
        $this->assertIsArray($parameters['name']);
        $this->assertIsArray($parameters['age']);
        $this->assertIsArray($parameters['format']);
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
        $resource = new FakeNestedInputResourceIntegration();
        $result = ($this->optionsMethods)($resource, 'Post');

        $this->assertArrayHasKey('request', $result);
        $this->assertIsArray($result['request']);
        $this->assertArrayHasKey('parameters', $result['request']);
        $parameters = $result['request']['parameters'];
        $this->assertIsArray($parameters);

        // Check parent parameters
        $this->assertArrayHasKey('firstName', $parameters);
        $this->assertArrayHasKey('lastName', $parameters);
        $this->assertIsArray($parameters['firstName']);
        $this->assertSame('user', $parameters['firstName']['group']);

        // Check nested parameters
        $this->assertArrayHasKey('city', $parameters);
        $this->assertArrayHasKey('zipCode', $parameters);
        $this->assertIsArray($parameters['city']);
        $this->assertSame('address', $parameters['city']['group']);
    }

    public function testOptionsWithRequiredParameters(): void
    {
        $resource = new FakeRequiredInputResourceIntegration();
        $result = ($this->optionsMethods)($resource, 'Get');

        $this->assertArrayHasKey('request', $result);
        $this->assertIsArray($result['request']);
        $this->assertArrayHasKey('required', $result['request']);

        $required = $result['request']['required'];
        $this->assertIsArray($required);
        $this->assertContains('query', $required);
        $this->assertNotContains('category', $required); // has default
    }
}
