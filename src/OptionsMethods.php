<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Named;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FilesParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

use function file_exists;
use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @psalm-import-type Body from Types
 * @psalm-import-type InsMap from Types
 * @psalm-import-type OptionsMethodsResponse from Types
 * @psalm-import-type OptionsResponse from Types
 */
final readonly class OptionsMethods
{
    /**
     * Constants for annotation name and "in" name
     */
    private const WEB_CONTEXT_NAME = [
        CookieParam::class => 'cookie',
        EnvParam::class => 'env',
        FormParam::class => 'formData',
        QueryParam::class => 'query',
        ServerParam::class => 'server',
        FilesParam::class => 'files',
    ];

    /** @psalm-pure */
    public function __construct(
        #[Named('json_schema_dir')]
        private string $schemaDir = '',
    ) {
    }

    /**
     * @return OptionsMethodsResponse
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function __invoke(ResourceObject $ro, string $requestMethod): array
    {
        $method = new ReflectionMethod($ro::class, 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        [$doc, $paramDoc] = (new OptionsMethodDocBolck())($method);
        $methodOption = $doc;
        $paramMetas = (new OptionsMethodRequest())($method, $paramDoc, $ins);
        $schema = $this->getJsonSchema($method);
        $request = ! empty($paramMetas) ? ['request' => $paramMetas] : [];
        $methodOption += $request;
        if (! empty($schema)) {
            $methodOption += ['schema' => $schema];
        }

        $extras = $this->getMethodExtras($method);
        if (! empty($extras)) {
            $methodOption += $extras;
        }

        return $methodOption; // @phpstan-ignore-line
    }

    /**
     * @return (Embed|Link)[][]
     * @psalm-return array{links?: non-empty-list<Link>, embed?: non-empty-list<Embed>}
     * @phpstan-return (Embed|Link)[][]
     *
     * @psalm-capabilities write-props|read-globals|write-globals|io|time
     */
    private function getMethodExtras(ReflectionMethod $method): array
    {
        $extras = [];
        $annotations = $method->getAnnotations();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $extras['links'][] = $annotation;
            }

            if (! ($annotation instanceof Embed)) {
                continue;
            }

            $extras['embed'][] = $annotation;
        }

        return $extras;
    }

    /**
     * @return InsMap
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    private function getInMap(ReflectionMethod $method): array
    {
        $ins = [];

        return $this->getInsFromParameterAttributes($method, $ins);
    }

    /**
     * @return Body
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    private function getJsonSchema(ReflectionMethod $method): array
    {
        $schema = $method->getAnnotation(JsonSchema::class);
        if (! $schema instanceof JsonSchema) {
            return [];
        }

        $schemaFile = $this->schemaDir . '/' . $schema->schema;
        if (! file_exists($schemaFile)) {
            return [];
        }

        return (array) json_decode((string) file_get_contents($schemaFile), null, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param InsMap $ins
     *
     * @return InsMap
     *
     * @psalm-suppress InvalidArrayOffset, MixedAssignment, MixedReturnTypeCoercion
     *
     * @psalm-capabilities write-props|read-globals|write-globals|io|time
     */
    public function getInsFromParameterAttributes(ReflectionMethod $method, array $ins): array
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $attributes = $parameter->getAttributes();
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (! ($instance instanceof AbstractWebContextParam)) {
                    continue;
                }

                /** @var array-key $class */
                /** @phpstan-ignore varTag.nativeType */
                $class = $instance::class;
                $ins[$parameter->name] = self::WEB_CONTEXT_NAME[$class];
            }
        }

        return $ins;
    }
}
