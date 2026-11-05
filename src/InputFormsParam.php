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
use function assert;
use function count;
use function is_array;

/**
 * @psalm-import-type Query from Types
 * @psalm-import-type RequestQuery from Types
 */
final readonly class InputFormsParam implements ParamInterface
{
    /** @param array<ReflectionAttribute<InputFile>> $inputFileAttributes */
    public function __construct(
        private FileUploadFactoryInterface $factory,
        private ReflectionParameter $parameter,
        private array $inputFileAttributes = [],
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Returns array of FileUpload objects using createMultiple()
     *
     * @param RequestQuery $query
     *
     * @return array<AbstractFileUpload>
     *
     * @psalm-taint-source input
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector): array
    {
        if (array_key_exists($varName, $query)) {
            return $this->getUserFileUploads($varName, $query);
        }

        $inputFileAttribute = $this->inputFileAttributes[0] ?? null;

        return $this->factory->createMultiple($this->parameter, $query, $inputFileAttribute);
    }

    /**
     * @param RequestQuery $query
     *
     * @return array<AbstractFileUpload>
     *
     * @throws InvalidArgumentException When array contains invalid file objects.
     *
     * @psalm-capabilities pure
     */
    private function getUserFileUploads(string $varName, array $query): array
    {
        $fileUploads = $query[$varName];
        if (! is_array($fileUploads)) {
            throw new InvalidArgumentException($varName); // Invalid type of array
        }

        /** @var array<AbstractFileUpload> $fileUploads */

        assert(count($fileUploads) > 0);
        foreach ($fileUploads as $fileUpload) {
            if (! ($fileUpload instanceof FileUpload) && ! ($fileUpload instanceof ErrorFileUpload)) {
                throw new InvalidArgumentException($varName); // Invalid item of array tye of FileUpload or ErrorFileUpload
            }
        }

        return $fileUploads;
    }
}
