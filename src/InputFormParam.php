<?php

declare(strict_types=1);

namespace BEAR\Resource;

use InvalidArgumentException;
use Koriym\FileUpload\AbstractFileUpload;
use Koriym\FileUpload\ErrorFileUpload;
use Koriym\FileUpload\FileUpload;
use Override;
use Ray\Di\InjectorInterface;
use Ray\InputQuery\Attribute\InputFile;
use Ray\InputQuery\FileUploadFactoryInterface;
use ReflectionAttribute;
use ReflectionParameter;

use function array_key_exists;

/**
 * @psalm-import-type Query from Types
 * @psalm-import-type RequestQuery from Types
 */
final readonly class InputFormParam implements ParamInterface
{
    /**
     * @param array<ReflectionAttribute<InputFile>> $inputFileAttributes
     *
     * @psalm-pure
     */
    public function __construct(
        private FileUploadFactoryInterface $factory,
        private ReflectionParameter $parameter,
        private array $inputFileAttributes = [],
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Returns form metadata instead of parsing query data
     *
     * @param RequestQuery $query
     *
     * @psalm-taint-source input
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector): AbstractFileUpload|null
    {
        // When user sets the FileUpload object, return it directly
        if (array_key_exists($varName, $query)) {
            $value = $query[$varName];
            // Type check: ensure the value is a valid file upload object or null
            if ($value !== null && ! ($value instanceof FileUpload) && ! ($value instanceof ErrorFileUpload)) {
                throw new InvalidArgumentException($varName);
            }

            return $value;
        }

        unset($injector);
        $inputFileAttribute = $this->inputFileAttributes[0] ?? null;

        return $this->factory->create($this->parameter, $query, $inputFileAttribute);
    }
}
