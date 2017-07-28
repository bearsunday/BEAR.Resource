<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\JsonSchemaErrorException;
use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\ResourceObject;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\WeavedInterface;
use Ray\Di\Di\Named;

final class JsonSchemaInterceptor implements MethodInterceptor
{
    /**
     * @var string
     */
    private $schemaDir;

    /**
     * @var string
     */
    private $validateDir;

    /**
     * @param string $schemaDir
     * @param string $validateDir
     *
     * @Named("schemaDir=json_schema_dir,validateDir=json_validate_dir")
     */
    public function __construct($schemaDir, $validateDir)
    {
        $this->schemaDir = $schemaDir;
        $this->validateDir = $validateDir;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->proceed();
        /* @var $ro \BEAR\Resource\ResourceObject */
        if ($ro->code !== 200) {
            return $ro;
        }
        $jsonSchema = $invocation->getMethod()->getAnnotation(JsonSchema::class);
        /* @var $jsonSchema JsonSchema */
        if ($jsonSchema->params) {
            $this->validateRequest($jsonSchema, $ro);
        }
        $this->validateResponse($jsonSchema, $ro);

        return $ro;
    }

    /**
     * @return string
     */
    private function validateRequest(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        $schemaFile = $this->validateDir . '/' . $jsonSchema->params;
        $this->validateFileExists($schemaFile);
        $this->validate((object) $ro->uri->query, $schemaFile);
    }

    private function validateResponse(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        $schemeFile = $this->getSchemaFile($jsonSchema, $ro);
        $body = isset($ro->body[$jsonSchema->key]) ? (object) $ro->body[$jsonSchema->key] : (object) $ro->body;
        $this->validate($body, $schemeFile);
    }

    private function validate($scanObject, $schemaFile)
    {
        $validator = new Validator;
        $schema = (object) ['$ref' => 'file://' . $schemaFile];
        $validator->validate($scanObject, $schema, Constraint::CHECK_MODE_TYPE_CAST);
        $isValid = $validator->isValid();
        if ($isValid) {
            return;
        }
        $e = null;
        foreach ($validator->getErrors() as $error) {
            $msg = sprintf('[%s] %s', $error['property'], $error['message']);
            $e = $e ? new JsonSchemaErrorException($msg, 0, $e) : new JsonSchemaErrorException($msg);
        }
        throw new JsonSchemaException($schemaFile, Code::ERROR, $e);
    }

    private function getSchemaFile(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        if (! $jsonSchema->schema) {
            // for BC only
            $ref = new \ReflectionClass($ro);
            $roFileName = $ro instanceof WeavedInterface ? $roFileName = $ref->getParentClass()->getFileName() : $ref->getFileName();
            $bcFile = str_replace('.php', '.json', $roFileName);
            if (file_exists($bcFile)) {
                return $bcFile;
            }
        }
        $schemaFile = $this->schemaDir . '/' . $jsonSchema->schema;
        $this->validateFileExists($schemaFile);

        return $schemaFile;
    }

    /**
     * @param $schemaFile
     */
    private function validateFileExists($schemaFile)
    {
        if (! file_exists($schemaFile) || is_dir($schemaFile)) {
            throw new JsonSchemaNotFoundException($schemaFile);
        }
    }
}
