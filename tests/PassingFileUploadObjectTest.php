<?php

/**
 * @psalm-import-type FileUploadSuccessResponse from Types
 * @psalm-import-type MultipleFileUploadResponse from Types
 */

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Module\ResourceModule;
use Koriym\FileUpload\FileUpload;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function filesize;

use const UPLOAD_ERR_OK;

class PassingFileUploadObjectTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $module = new ResourceModule('FakeVendor\Sandbox');
        $module->override(new FakeSchemeModule());
        $injector = new Injector($module);
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testInputFormParamInvalidStringType(): void
    {
        $this->expectException(ParameterException::class);

        $this->resource->post('app://self/file-upload', ['image' => 'invalid_string']);
    }

    public function testInputFormParamInvalidIntType(): void
    {
        $this->expectException(ParameterException::class);

        $this->resource->post('app://self/file-upload', ['image' => 123]);
    }

    public function testInputFormParamInvalidArrayType(): void
    {
        $this->expectException(ParameterException::class);

        $this->resource->post('app://self/file-upload', ['image' => []]);
    }

    public function testInputFormsParamInvalidNonArrayType(): void
    {
        $this->expectException(ParameterException::class);
        $this->resource->put('app://self/file-upload', ['files' => 'not_an_array']);
    }

    public function testInputFormsParamInvalidArrayElementString(): void
    {
        $this->expectException(ParameterException::class);
        $this->resource->put('app://self/file-upload', ['files' => ['invalid_string']]);
    }

    public function testInputFormsParamInvalidArrayElementInt(): void
    {
        $this->expectException(ParameterException::class);
        $this->resource->put('app://self/file-upload', [
            'files' => [
                FileUpload::fromFile(__DIR__ . '/Fake/app.svg'),
                123,
            ],
        ]);
    }

    public function testExtractArrayFilesNonNumericIndex(): void
    {
        // Test with non-numeric index (should be ignored)
        $file1 = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');

        $request = $this->resource->put('app://self/file-upload', ['files[abc]' => $file1]);

        /** @var FileUpload $result */
        $result = $request;

        // Since non-numeric index is ignored, it falls back to factory which creates empty array
        $this->assertSame(200, $result->code);
        $this->assertSame(0, $result->body['total']);
    }

    public function testExtractArrayFilesPartialMatch(): void
    {
        // Test keys that partially match but aren't array indexes
        $file1 = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');

        /** @var FileUpload $result */
        $result = $this->resource->put('app://self/file-upload', [
            'files_other' => $file1,  // Doesn't match files[x] pattern
            'other_files[0]' => $file1,  // Different prefix
        ]);

        // No matching array keys, falls back to factory
        $this->assertSame(200, $result->code);
        $this->assertSame(0, $result->body['total']);
    }

    public function testInputFormParamFactoryFallback(): void
    {
        // Test that factory is called when no Service Locator data is present
        // This ensures the factory path is covered
        $_FILES = [
            'image' => [
                'name' => 'test.svg',
                'type' => 'image/svg+xml',
                'size' => filesize(__DIR__ . '/Fake/app.svg'),
                'tmp_name' => __DIR__ . '/Fake/app.svg',
                'error' => UPLOAD_ERR_OK,
            ],
        ];

        $result = $this->resource->post('app://self/file-upload', []);

        /** @var FileUpload $result */

        $this->assertSame(200, $result->code);
        $this->assertTrue($result->body['success']);
        $this->assertSame('test.svg', $result->body['filename']);

        // Clean up
        $_FILES = [];
    }

    public function testInputFormsParamFactoryFallback(): void
    {
        // Test that factory is called when no Service Locator data is present
        $_FILES = [
            'files' => [
                'name' => ['test1.svg', 'test2.png'],
                'type' => ['image/svg+xml', 'image/png'],
                'size' => [filesize(__DIR__ . '/Fake/app.svg'), filesize(__DIR__ . '/Fake/app.png')],
                'tmp_name' => [__DIR__ . '/Fake/app.svg', __DIR__ . '/Fake/app.png'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],
        ];

        /** @var FileUpload $result */
        $result = $this->resource->put('app://self/file-upload', []);

        $this->assertSame(200, $result->code);
        $this->assertSame(2, $result->body['total']);

        // Clean up
        $_FILES = [];
    }
}
