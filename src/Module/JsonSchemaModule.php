<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Interceptor\JsonSchemaInterceptor;
use BEAR\Resource\ResourceObject;
use Ray\Di\AbstractModule;

class JsonSchemaModule extends AbstractModule
{
    /**
     * @var string
     */
    private $jsonSchemaDir;

    /**
     * @var string
     */
    private $jsonValidateDir;

    /**
     * @var string
     */
    private $jsonSchemaHost;

    /**
     * @param string              $jsonSchemaDir   Json-schema json file directory
     * @param string              $jsonValidateDir Json-schema validator json file directory
     * @param string              $jsonSchemaHost  Json-schema host name ex) https://example.com/schema/
     * @param AbstractModule|null $module
     */
    public function __construct(
        string $jsonSchemaDir = '',
        string $jsonValidateDir = '',
        string $jsonSchemaHost = '',
        AbstractModule $module = null
    ) {
        $this->jsonSchemaDir = $jsonSchemaDir;
        $this->jsonValidateDir = $jsonValidateDir;
        $this->jsonSchemaHost = $jsonSchemaHost;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith('json_schema_dir')->toInstance($this->jsonSchemaDir);
        $this->bind()->annotatedWith('json_validate_dir')->toInstance($this->jsonValidateDir);
        $this->bind()->annotatedWith('json_schema_host')->toInstance($this->jsonSchemaHost);

        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->annotatedWith(JsonSchema::class),
            [JsonSchemaInterceptor::class]
        );
    }
}
