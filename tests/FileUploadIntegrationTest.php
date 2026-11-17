<?php

/**
 * Test-specific types for FileUploadIntegrationTest
 *
 * @psalm-type FileUploadSuccessResponse = array{
 *     success: bool,
 *     filename: string,
 *     size: int,
 *     type: string,
 *     title?: string
 * }
 * @psalm-type FileUploadErrorResponse = array{
 *     error: bool,
 *     message: string,
 *     title?: string,
 *     username?: string
 * }
 * @psalm-type FileUploadItemResponse = array{
 *     success?: bool,
 *     error?: bool,
 *     filename?: string,
 *     size?: int,
 *     type?: string,
 *     message?: string
 * }
 * @psalm-type MultipleFileUploadResponse = array{
 *     files: list<FileUploadItemResponse>,
 *     total: int
 * }
 * @psalm-type UserProfileResponse = array{
 *     username: string,
 *     avatar: array{filename: string, size: int, type: string}|null
 * }
 * @psalm-type GalleryImageResponse = array{
 *     index: int,
 *     filename: string,
 *     size: int,
 *     type: string,
 *     isImage: bool
 * }
 * @psalm-type GalleryErrorResponse = array{
 *     index: int,
 *     error: string
 * }
 * @psalm-type GalleryUploadResponse = array{
 *     galleryName: string,
 *     totalImages: int,
 *     validImages: list<GalleryImageResponse>,
 *     errorImages: list<GalleryErrorResponse>,
 *     hasErrors: bool
 * }
 */

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Koriym\FileUpload\ErrorFileUpload;
use Koriym\FileUpload\FileUpload;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function filesize;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_OK;

class FileUploadIntegrationTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $module = new ResourceModule('FakeVendor\Sandbox');
        $module->override(new FakeSchemeModule());
        $injector = new Injector($module);
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testSingleFileUploadWithServiceLocator(): void
    {
        // Test Service Locator pattern: pass FileUpload object directly in query
        $fileUpload = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');

        $request = $this->resource->post('app://self/file-upload', [
            'image' => $fileUpload,  // Direct object for Service Locator
            'title' => 'Test Upload',
        ]);

        /** @var ResourceObject $result */
        $result = $request;

        $this->assertSame(200, $result->code);
        /** @phpstan-var array{success: bool, filename: string, size: int, type: string, title: string} */
        $body = $result->body;
        $this->assertTrue($body['success']);
        $this->assertSame('app.svg', $body['filename']);
        $this->assertSame('image/svg+xml', $body['type']);
        $this->assertSame('Test Upload', $body['title']);
    }

    public function testSingleFileUploadWithHttpSimulation(): void
    {
        // Test $_FILES simulation: set $_FILES global for HTTP upload simulation
        $_FILES = [
            'image' => [
                'name' => 'app.svg',
                'type' => 'image/svg+xml',
                'size' => filesize(__DIR__ . '/Fake/app.svg'),
                'tmp_name' => __DIR__ . '/Fake/app.svg',
                'error' => UPLOAD_ERR_OK,
            ],
        ];

        /** @var FileUpload $result */
        $result = $this->resource->post('app://self/file-upload', ['title' => 'HTTP Upload Test']);
        $this->assertSame(200, $result->code);
        $body = $result->body;
        $this->assertTrue($body['success']);
        $this->assertSame('app.svg', $body['filename']);
        $this->assertSame('image/svg+xml', $body['type']);
        $this->assertSame('HTTP Upload Test', $body['title']);

        // Clean up
        $_FILES = [];
    }

    public function testSingleFileUploadValidationErrorWithServiceLocator(): void
    {
        // Test Service Locator with validation error - create ErrorFileUpload directly
        $largeFileData = [
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'size' => 2 * 1024 * 1024, // 2MB - exceeds 1MB limit
            'tmp_name' => '/tmp/fake',
            'error' => UPLOAD_ERR_OK,
        ];
        $errorFileUpload = new ErrorFileUpload($largeFileData, 'File size exceeds maximum allowed size');

        /** @var FileUpload $result */
        $result = $this->resource->post('app://self/file-upload', ['image' => $errorFileUpload]);

        $this->assertSame(400, $result->code);
        $body = $result->body;
        $this->assertTrue($body['error']);
        $this->assertStringContainsString('exceeds maximum allowed size', $body['message']);
    }

    public function testMultipleFileUploadWithServiceLocator(): void
    {
        // Test multiple files with Service Locator pattern
        $file1 = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');
        $file2 = FileUpload::create([
            'name' => 'test.png',
            'type' => 'image/png',
            'size' => filesize(__DIR__ . '/Fake/app.png'),
            'tmp_name' => __DIR__ . '/Fake/app.png',
            'error' => UPLOAD_ERR_OK,
        ]);

        /** @var FileUpload $result */
        $result = $this->resource->put('app://self/file-upload', [
            'files' => [$file1, $file2],
        ]);

        $this->assertSame(200, $result->code);
        $body = $result->body;
        $this->assertSame(2, $body['total']);
        $this->assertCount(2, $body['files']);
        $this->assertTrue($body['files'][0]['success']);
        $this->assertSame('app.svg', $body['files'][0]['filename']);
    }

    public function testMultipleFileUploadWithHttpSimulation(): void
    {
        // Test multiple files with $_FILES simulation (proper array format)
        $_FILES = [
            'files' => [
                'name' => ['app.svg', 'test.png'],
                'type' => ['image/svg+xml', 'image/png'],
                'size' => [filesize(__DIR__ . '/Fake/app.svg'), filesize(__DIR__ . '/Fake/app.png')],
                'tmp_name' => [__DIR__ . '/Fake/app.svg', __DIR__ . '/Fake/app.png'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            ],
        ];

        /** @var FileUpload $result */
        $result = $this->resource->put('app://self/file-upload', []);
        $this->assertSame(200, $result->code);
        $body = $result->body;
        $this->assertSame(2, $body['total']);
        $this->assertCount(2, $body['files']);
        $this->assertTrue($body['files'][0]['success']);
        $this->assertSame('app.svg', $body['files'][0]['filename']);

        // Clean up
        $_FILES = [];
    }

    public function testOptionalFileUploadWithoutFile(): void
    {
        // Test without file - use null explicitly for Service Locator
        $request = $this->resource->patch('app://self/file-upload', [
            'avatar' => null,
            'username' => 'testuser',
        ]);

        /** @var FileUpload $result */
        $result = $request;

        $this->assertSame(200, $result->code);
        $body = $result->body;
        $this->assertSame('testuser', $body['username']);
        $this->assertNull($body['avatar']);
    }

    public function testOptionalFileUploadWithFile(): void
    {
        // Test with file
        $fileUpload = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');

        $request = $this->resource->patch('app://self/file-upload', [
            'avatar' => $fileUpload,
            'username' => 'testuser2',
        ]);

        /** @var FileUpload $result */
        $result = $request;

        $this->assertSame(200, $result->code);
        $body = $result->body;
        $this->assertSame('testuser2', $body['username']);
        $this->assertSame('app.svg', $body['avatar']['filename']);
    }

    public function testGalleryUploadWithServiceLocator(): void
    {
        // Create mixed results - valid and invalid files
        $validFile = FileUpload::fromFile(__DIR__ . '/Fake/app.svg');
        $invalidFile = new ErrorFileUpload([
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'size' => 1024 * 1024, // 1MB - exceeds 512KB limit
            'tmp_name' => '/tmp/large',
            'error' => UPLOAD_ERR_CANT_WRITE,
        ]);

        /** @var FileUpload $result */
        $result = $this->resource->delete('app://self/file-upload', [
            'images' => [$validFile, $invalidFile],
            'galleryName' => 'Test Gallery',
        ]);

        $this->assertSame(207, $result->code); // Multi-Status
        $body = $result->body;
        $this->assertSame('Test Gallery', $body['galleryName']);
        $this->assertSame(2, $body['totalImages']);
        $this->assertCount(1, $body['validImages']);
        $this->assertCount(1, $body['errorImages']);
        $this->assertTrue($body['hasErrors']);
        $this->assertTrue($body['validImages'][0]['isImage']);
    }
}
