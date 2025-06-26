<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\InputAttributeIterator;
use BEAR\Resource\ResourceObject;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Named;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FilesParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

use function array_filter;
use function array_merge;
use function array_unique;
use function file_exists;
use function file_get_contents;
use function in_array;
use function json_decode;

use const ARRAY_FILTER_USE_KEY;
use const JSON_THROW_ON_ERROR;

/**
 * @psalm-type WebContextKey = class-string<AbstractWebContextParam>
 * @psalm-type WebContextValue = 'cookie'|'env'|'formData'|'query'|'server'|'files'
 * @psalm-type OptionParamDoc = array{description?: string, embed?: mixed, links?: mixed, request?: mixed, schema?: mixed, summary?: string}
 */
final class OptionsMethods
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

    public function __construct(
        private readonly InputParamMetaInterface $inputParamMeta,
        #[Named('json_schema_dir')]
        private readonly string $schemaDir = '',
    ) {
    }

    /** @return OptionParamDoc */
    public function __invoke(ResourceObject $ro, string $requestMethod): array
    {
        $method = new ReflectionMethod($ro::class, 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        [$doc, $paramDoc] = (new OptionsMethodDocBolck())($method);
        $methodOption = $doc;
        $paramMetas = (new OptionsMethodRequest())($method, $paramDoc, $ins);
        $inputMetas = $this->inputParamMeta->get($method);
        $paramMetas = $this->mergeParameterMetas($paramMetas, $inputMetas, $method);
        $schema = $this->getJsonSchema($method);
        $request = $paramMetas ? ['request' => $paramMetas] : [];
        $methodOption += $request;
        if (! empty($schema)) {
            $methodOption += ['schema' => $schema];
        }

        $extras = $this->getMethodExtras($method);
        if (! empty($extras)) {
            $methodOption += $extras;
        }

        /** @var OptionParamDoc $methodOption */
        return $methodOption;
    }

    /**
     * @return (Embed|Link)[][]
     * @psalm-return array{links?: non-empty-list<Link>, embed?: non-empty-list<Embed>}
     * @phpstan-return (Embed|Link)[][]
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

    /** @return array<string, string> */
    private function getInMap(ReflectionMethod $method): array
    {
        $ins = [];
        bc_for_annotation: {
            // @codeCoverageIgnoreStart
            $annotations = $method->getAnnotations();
            $ins = $this->getInsFromMethodAnnotations($annotations, $ins);
        if ($ins) {
            return $ins;
        }
            // @codeCoverageIgnoreEnd
        }

        /** @var array<string, string> $insParam */
        $insParam = $this->getInsFromParameterAttributes($method, $ins);

        return $insParam;
    }

    /** @return array<string, mixed> */
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

        /** @var array<string, mixed> $schema */
        $schema = (array) json_decode((string) file_get_contents($schemaFile), null, 512, JSON_THROW_ON_ERROR);

        return $schema;
    }

    /**
     * @param array<object>         $annotations
     * @param array<string, string> $ins
     *
     * @return array<string, string>
     *
     * @codeCoverageIgnore BC for annotation
     */
    public function getInsFromMethodAnnotations(array $annotations, array $ins): array
    {
        foreach ($annotations as $annotation) {
            if (! ($annotation instanceof AbstractWebContextParam)) {
                continue;
            }

            $class = $annotation::class;
            if (! isset(self::WEB_CONTEXT_NAME[$class])) {
                continue;
            }

            $ins[$annotation->param] = self::WEB_CONTEXT_NAME[$class];
        }

        return $ins;
    }

    /**
     * @param array<string, string> $ins
     *
     * @return array<string, string>
     */
    public function getInsFromParameterAttributes(ReflectionMethod $method, array $ins): array|null
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $attributes = $parameter->getAttributes();
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (! ($instance instanceof AbstractWebContextParam)) {
                    continue;
                }

                $class = $instance::class;
                if (! isset(self::WEB_CONTEXT_NAME[$class])) {
                    continue;
                }

                $webContextName = self::WEB_CONTEXT_NAME[$class];
                $ins[$parameter->name] = $webContextName;
            }
        }

        return $ins;
    }

    /**
     * Merge parameter metadata from OptionsMethodRequest and InputParamMeta
     *
     * @param array{parameters?: array<string, array<string, mixed>>, required?: array<int, string>} $regularParams
     * @param array{parameters?: array<string, array<string, mixed>>, required?: array<int, string>} $inputParams
     *
     * @return array{parameters?: array<string, array<string, mixed>>, required?: array<int, string>}
     */
    private function mergeParameterMetas(array $regularParams, array $inputParams, \ReflectionMethod $method): array
    {
        if (empty($inputParams)) {
            return $regularParams;
        }

        // Filter out Input attribute parameters from regular parameters
        $filteredParams = $regularParams;
        if (isset($filteredParams['parameters'])) {
            $filteredParams['parameters'] = $this->filterInputAttributeParameters($filteredParams['parameters'], $method);
        }

        $merged = [];

        // Merge parameters
        $allParameters = [];
        if (isset($filteredParams['parameters'])) {
            $allParameters = array_merge($allParameters, $filteredParams['parameters']);
        }

        if (isset($inputParams['parameters'])) {
            $allParameters = array_merge($allParameters, $inputParams['parameters']);
        }

        if (! empty($allParameters)) {
            $merged['parameters'] = $allParameters;
        }

        // Merge required parameters
        $allRequired = [];
        if (isset($filteredParams['required'])) {
            $allRequired = array_merge($allRequired, $filteredParams['required']);
        }

        if (isset($inputParams['required'])) {
            $allRequired = array_merge($allRequired, $inputParams['required']);
        }

        if (! empty($allRequired)) {
            $merged['required'] = array_unique($allRequired);
        }

        return $merged;
    }

    /**
     * Filter out parameters that have Input attributes
     *
     * @param array<string, array<string, mixed>> $parameters
     *
     * @return array<string, array<string, mixed>>
     */
    private function filterInputAttributeParameters(array $parameters, \ReflectionMethod $method): array
    {
        $inputIterator = new InputAttributeIterator();

        $inputParamNames = [];
        foreach ($inputIterator($method) as $paramName => $param) {
            unset($param);
            $inputParamNames[] = $paramName;
        }

        return array_filter($parameters, static function ($key) use ($inputParamNames) {
            return ! in_array($key, $inputParamNames, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}
