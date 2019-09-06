<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Named;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FilesParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

final class OptionsMethods
{
    /**
     * Constants for annotation name and "in" name
     *
     * @var array
     */
    const WEB_CONTEXT_NAME = [
        CookieParam::class => 'cookie',
        EnvParam::class => 'env',
        FormParam::class => 'formData',
        QueryParam::class => 'query',
        ServerParam::class => 'server',
        FilesParam::class => 'files'
    ];

    private $reader;

    /**
     * @var string
     */
    private $schemaDir;

    /**
     * @Named("schemaDir=json_schema_dir")
     */
    public function __construct(Reader $reader, string $schemaDir = '')
    {
        $this->reader = $reader;
        $this->schemaDir = $schemaDir;
    }

    public function __invoke(ResourceObject $ro, string $requestMethod) : array
    {
        $method = new \ReflectionMethod(get_class($ro), 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        list($doc, $paramDoc) = (new OptionsMethodDocBolck)($method);
        $methodOption = $doc;
        $paramMetas = (new OptionsMethodRequest($this->reader))($method, $paramDoc, $ins);
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

        return $methodOption;
    }

    private function getMethodExtras(\ReflectionMethod $method) : array
    {
        $extras = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $extras['links'][] = $annotation;
            }
            if ($annotation instanceof Embed) {
                $extras['embed'][] = $annotation;
            }
        }

        return $extras;
    }

    private function getInMap(\ReflectionMethod $method) : array
    {
        $ins = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AbstractWebContextParam) {
                $ins[$annotation->param] = self::WEB_CONTEXT_NAME[get_class($annotation)];
            }
        }

        return $ins;
    }

    private function getJsonSchema(\ReflectionMethod $method) : array
    {
        $schema = $this->reader->getMethodAnnotation($method, JsonSchema::class);
        if (! $schema instanceof JsonSchema) {
            return [];
        }
        $schemaFile = $this->schemaDir . '/' . $schema->schema;
        if (! file_exists($schemaFile)) {
            return [];
        }

        return (array) json_decode((string) file_get_contents($schemaFile));
    }
}
