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
use BEAR\Resource\ResourceObject;
use JsonSchema\Validator;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\WeavedInterface;

final class JsonSchemaInterceptor implements MethodInterceptor
{
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
        $ref = new \ReflectionClass($ro);
        $roFileName = $ro instanceof WeavedInterface ? $roFileName = $ref->getParentClass()->getFileName() : $ref->getFileName();
        $scanObject = $this->getBodyAsObject($jsonSchema, $ro);
        if ($jsonSchema->validate === 'response' | $jsonSchema->validate === 'request') {
            $this->validate($scanObject, $jsonSchema, '.php', $roFileName);
        }

        return $ro;
    }

    /**
     * @param JsonSchema     $jsonSchema
     * @param ResourceObject $ro
     * @param string         $ext
     * @param string         $schemaFile
     */
    public function validate($scanObject, JsonSchema $jsonSchema, $ext, $thisFile)
    {
        $validator = new Validator;
        $schemaFile = str_replace($ext, '.json', $thisFile);
        $validator->validate($scanObject, (object) ['$ref' => 'file://' . $schemaFile]);
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

    /**
     * @return object
     */
    private function getBodyAsObject(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        if ($jsonSchema->key && isset($ro->body[$jsonSchema->key])) {
            return (object) $ro->body[$jsonSchema->key  ];
        }

        return (object) $ro->body;
    }
}
