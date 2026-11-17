<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\RequestParamInterface;
use BEAR\Resource\Annotation\ResourceParam;
use Override;
use Ray\Aop\ReflectionMethod;
use Ray\InputQuery\Attribute\Input;
use Ray\InputQuery\Attribute\InputFile;
use Ray\InputQuery\FileUploadFactoryInterface;
use Ray\InputQuery\InputQueryInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @psalm-import-type ParamMap from Types
 * @psalm-import-type WebContextParamMap from Types
 * @psalm-import-type ReflectionParameterMap from Types
 * @psalm-import-type ObjectList from Types
 */
final readonly class NamedParamMetas implements NamedParamMetasInterface
{
    /**
     * @param InputQueryInterface<object> $inputQuery
     *
     * @psalm-pure
     */
    public function __construct(
        private InputQueryInterface $inputQuery,
        private FileUploadFactoryInterface $factory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function __invoke(callable $callable): array
    {
        // callable is [object, string] but native type doesn't allow array access
        /** @psalm-suppress InvalidArrayAccess, MixedArgument */
        /** @var array{0:object, 1:string} $callable */
        $method = new ReflectionMethod($callable[0], $callable[1]); // @phpstan-ignore-line

        return $this->getAttributeParamMetas($method);
    }

    /**
     * @return ParamMap
     *
     * @psalm-suppress TooManyTemplateParams $refAttribute
     * @psalm-suppress PossiblyInvalidArrayAssignment
     */
    private function getAttributeParamMetas(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();
        $names = $valueParams = [];

        // Check method-level ResourceParam attributes
        $methodResourceParams = $method->getAttributes(ResourceParam::class);
        foreach ($methodResourceParams as $methodAttr) {
            $resourceParam = $methodAttr->newInstance();
            $names[$resourceParam->param] = new AssistedResourceParam($resourceParam);
        }

        foreach ($parameters as $parameter) {
            // Skip if already set by method-level attribute
            if (isset($names[$parameter->name])) {
                continue;
            }

            $refAttribute = $parameter->getAttributes(RequestParamInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($refAttribute) {
                /** @var ?ResourceParam $resourceParam */
                $resourceParam = $refAttribute[0]->newInstance();
                if ($resourceParam instanceof ResourceParam) {
                    $names[$parameter->name] = new AssistedResourceParam($resourceParam);
                    continue;
                }
            }

            $refWebContext = $parameter->getAttributes(AbstractWebContextParam::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($refWebContext) {
                $webParam = $refWebContext[0]->newInstance();
                $default = $this->getDefault($parameter);
                $param = new AssistedWebContextParam($webParam, $default);
                $names[$parameter->name] = $param;
                continue;
            }

            // #[Input]
            $inputAttribute = $parameter->getAttributes(Input::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($inputAttribute) {
                $names[$parameter->name] = new InputParam($this->inputQuery, $parameter);
                continue;
            }

            // #[InputFile]
            $inputFileAttributes = $parameter->getAttributes(InputFile::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($inputFileAttributes) {
                $this->setInputFileParam($parameter, $inputFileAttributes, $names);
                continue;
            }

            $valueParams[$parameter->name] = $parameter;
        }

        $names = $this->getNames($names, $valueParams);

        return $names;
    }

    /**
     * @param array<ReflectionAttribute<InputFile>> $inputFileAttributes
     * @param ParamMap                              $names
     *
     * @psalm-capabilities read-props|write-refs
     */
    private function setInputFileParam(ReflectionParameter $parameter, array $inputFileAttributes, array &$names): void
    {
        $type = $parameter->getType();
        $isArray = $type instanceof ReflectionNamedType && $type->isBuiltin() && $type->getName() === 'array';
        if ($isArray) {
            $names[$parameter->name] = new InputFormsParam($this->factory, $parameter, $inputFileAttributes);

            return;
        }

        $names[$parameter->name] = new InputFormParam($this->factory, $parameter, $inputFileAttributes);
    }

    /** @psalm-return DefaultParam<mixed>|NoDefaultParam */
    private function getDefault(ReflectionParameter $parameter): DefaultParam|NoDefaultParam
    {
        return $parameter->isDefaultValueAvailable() === true ? new DefaultParam($parameter->getDefaultValue()) : new NoDefaultParam();
    }

    /**
     * @param ParamMap               $names
     * @param ReflectionParameterMap $valueParams
     *
     * @return ParamMap
     */
    private function getNames(array $names, array $valueParams): array
    {
        foreach ($valueParams as $paramName => $valueParam) {
            $names[$paramName] = $this->getParam($valueParam);
        }

        return $names;
    }

    /**
     * @return ClassParam|OptionalParam|RequiredParam
     * @psalm-return ClassParam|OptionalParam<mixed>|RequiredParam
     */
    private function getParam(ReflectionParameter $parameter): ParamInterface
    {
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return new ClassParam($type, $parameter);
        }

        return $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam();
    }
}
