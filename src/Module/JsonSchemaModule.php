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
     * Json-schema json file directory
     *
     * @var string
     */
    private $jsonSchemaDir;

    /**
     * Json-schema validator json file directory
     *
     * @var string
     */
    private $jsonValidateDir;

    public function __construct($jsonSchemaDir = '', $jsonValidateDir = '', AbstractModule $module = null)
    {
        $this->jsonSchemaDir = $jsonSchemaDir;
        $this->jsonValidateDir = $jsonValidateDir;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith('json_schema_dir')->toInstance($this->jsonSchemaDir);
        $this->bind()->annotatedWith('json_validate_dir')->toInstance($this->jsonValidateDir);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->annotatedWith(JsonSchema::class),
            [JsonSchemaInterceptor::class]
        );
    }
}
