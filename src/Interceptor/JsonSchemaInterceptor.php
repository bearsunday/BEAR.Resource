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
        $result = $invocation->proceed();
        $object = $invocation->getThis();
        /* @var $object ResourceObject */
        if ($object->code !== 200) {
            return $result;
        }
        /* @var $result \BEAR\Resource\ResourceObject */
        $ref = new \ReflectionClass($object);
        $thisFile = $object instanceof WeavedInterface ? $thisFile = $ref->getParentClass()->getFileName() : $ref->getFileName();
        $schemaFile = str_replace('.php', '.json', $thisFile);
        $validator = new Validator;
        $jsonSchema = $invocation->getMethod()->getAnnotation(JsonSchema::class);
        $data = $this->getBodyAsObject($jsonSchema, $object);
        $validator->validate($data, (object) ['$ref' => 'file://' . $schemaFile]);
        $isValid = $validator->isValid();
        if ($isValid === true) {
            return $result;
        }

        $e = null;
        foreach ($validator->getErrors() as $error) {
            $msg = sprintf('[%s] %s', $error['property'], $error['message']);
            $e = $e ? new JsonSchemaErrorException($msg, 0, $e) : new JsonSchemaErrorException($msg);
        }
        throw new JsonSchemaException($schemaFile, Code::ERROR, $e);
    }

    private function getBodyAsObject(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        if ($jsonSchema->value && isset($ro->body[$jsonSchema->value])) {
            return (object) $ro->body[$jsonSchema->value];
        }

        return (object) $ro->body;
    }
}
