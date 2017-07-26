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
        $ref = new \ReflectionClass($ro);
        $thisFile = $ro instanceof WeavedInterface ? $thisFile = $ref->getParentClass()->getFileName() : $ref->getFileName();
        $schemaFile = str_replace('.php', '.json', $thisFile);
        $validator = new Validator;
        $jsonSchema = $invocation->getMethod()->getAnnotation(JsonSchema::class);
        $data = $this->getBodyAsObject($jsonSchema, $ro);
        $validator->validate($data, (object) ['$ref' => 'file://' . $schemaFile]);
        $isValid = $validator->isValid();
        if ($isValid === true) {
            return $ro;
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
            return (object) $ro->body[$jsonSchema->key];
        }

        return (object) $ro->body;
    }
}
