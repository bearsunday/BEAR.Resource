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
use ReflectionMethod;

use function assert;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function get_class;
use function json_decode;

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

    /** @var Reader */
    private $reader;

    /** @var string */
    private $schemaDir;

    /**
     * @Named("schemaDir=json_schema_dir")
     */
    public function __construct(Reader $reader, string $schemaDir = '')
    {
        $this->reader = $reader;
        $this->schemaDir = $schemaDir;
    }

    /**
     * return array{summary?: string, description?: string, request: array, links: array, embed: array}
     *
     * @return array<int|string, array|string>
     */
    public function __invoke(ResourceObject $ro, string $requestMethod): array
    {
        $method = new ReflectionMethod(get_class($ro), 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        [$doc, $paramDoc] = (new OptionsMethodDocBolck())($method);
        $methodOption = $doc;
        $paramMetas = (new OptionsMethodRequest($this->reader))($method, $paramDoc, $ins);
        $schema = $this->getJsonSchema($method);
        $request = $paramMetas ? ['request' => $paramMetas] : []; // @phpstan-ignore-line
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

    /**
     * @return (Embed|Link)[][]
     * @psalm-return array{links?: non-empty-list<Link>, embed?: non-empty-list<Embed>}
     * @phpstan-return (Embed|Link)[][]
     */
    private function getMethodExtras(ReflectionMethod $method): array
    {
        $extras = [];
        $annotations = $this->reader->getMethodAnnotations($method);
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
     * @return array<string, string>
     */
    private function getInMap(ReflectionMethod $method): array
    {
        $ins = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if (! ($annotation instanceof AbstractWebContextParam)) {
                continue;
            }

            $class = get_class($annotation);
            assert(class_exists($class));
            $ins[$annotation->param] = self::WEB_CONTEXT_NAME[$class];
        }

        return $ins;
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonSchema(ReflectionMethod $method): array
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
