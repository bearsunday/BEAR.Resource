<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;
use Ray\InputQuery\Attribute\InputFile;

final class InvalidFileUpload extends ResourceObject
{
    /**
     * Invalid: array<string> instead of array<FileUpload|ErrorFileUpload>
     *
     * @param array<string> $files
     */
    public function onPost(
        #[InputFile] array $files
    ): static {
        $this->body = ['error' => 'This should not work'];

        return $this;
    }

    /**
     * Invalid: array<int> instead of array<FileUpload|ErrorFileUpload>
     *
     * @param array<int> $numbers
     */
    public function onPut(
        #[InputFile] array $numbers
    ): static {
        $this->body = ['error' => 'This should not work'];

        return $this;
    }

    /**
     * Invalid: plain array without generic type
     */
    public function onPatch(
        #[InputFile] array $plainArray
    ): static {
        $this->body = ['error' => 'This should not work'];

        return $this;
    }

    /**
     * Invalid: array<mixed> - too generic
     *
     * @param array<mixed> $mixed
     */
    public function onDelete(
        #[InputFile] array $mixed
    ): static {
        $this->body = ['error' => 'This should not work'];

        return $this;
    }
}