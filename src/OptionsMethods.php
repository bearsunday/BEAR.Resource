<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\JsonSchema;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Named;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
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
        ServerParam::class => 'server'
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
        $method = new \ReflectionMethod($ro, 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        list($doc, $paramDoc) = (new OptionsMethodDocBolck)($method);
        $paramMetas = (new OptionsMethodRequest($this->reader))($method, $paramDoc, $ins);
        $schema = $this->getJsonSchema($method);
        $request = $paramMetas ? ['request' => $paramMetas] : [];
        if (! empty($schema)) {
            return $doc + $request + ['schema' => $schema];
        }

        return $doc + $request;
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

        return (array) json_decode(file_get_contents($schemaFile));
    }
}
