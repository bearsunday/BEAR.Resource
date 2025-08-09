<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\SemanticLog\Module\DevSemanticLoggerModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Throwable;

use function count;
use function file_get_contents;
use function glob;
use function json_decode;
use function sys_get_temp_dir;
use function unlink;

final class DevSemanticLoggerTest extends TestCase
{
    private string $logDirectory;
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->logDirectory = sys_get_temp_dir();

        // Create module with dev logging
        $module = new DevSemanticLoggerModule($this->logDirectory);
        $module->install(new TestModule());

        $injector = new Injector($module);
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    protected function tearDown(): void
    {
        // Clean up log files
        $logFiles = glob($this->logDirectory . '/semantic-dev-*.json');
        if ($logFiles === false) {
            return;
        }

        foreach ($logFiles as $file) {
            unlink($file);
        }
    }

    public function testDevLoggerCreatesFiles(): void
    {
        // Make resource call that should generate log files
        $resourceObject = $this->resource->get('app://self/simple', ['id' => 'dev-test']);

        $this->assertSame(200, $resourceObject->code);
        $this->assertStringContainsString('simple', (string) $resourceObject->uri);

        // Check that log files were created
        $logFiles = glob($this->logDirectory . '/semantic-dev-*.json');
        $this->assertNotFalse($logFiles, 'glob() should not return false');
        $this->assertGreaterThan(0, count($logFiles), 'Dev log files should be created');

        // Verify log file content
        $logFile = $logFiles[0];
        $this->assertFileExists($logFile);

        $logContent = file_get_contents($logFile);
        $this->assertNotFalse($logContent);

        $logData = json_decode($logContent, true);
        $this->assertIsArray($logData);
        $this->assertArrayHasKey('open', $logData);
        $this->assertArrayHasKey('close', $logData);

        // Check Profile structure
        if (! isset($logData['close']['context']['profile'])) {
            return;
        }

        $profile = $logData['close']['context']['profile'];
        $this->assertIsArray($profile);
        // Profile should have xhprof, xdebug, and php properties
        $this->assertArrayHasKey('php', $profile);
        $this->assertArrayHasKey('backtrace', $profile['php']);
    }

    public function testDevLoggerHandlesErrors(): void
    {
        // Test error handling
        try {
            $this->resource->get('app://self/error', ['id' => 'dev-error-test']);
            $this->fail('Expected exception was not thrown');
        } catch (Throwable) {
            // Expected exception
        }

        // Check that error log files were created
        $logFiles = glob($this->logDirectory . '/semantic-dev-*.json');
        $this->assertNotFalse($logFiles, 'glob() should not return false');
        $this->assertGreaterThan(0, count($logFiles), 'Dev error log files should be created');

        // Verify error log content
        $logFile = $logFiles[0];
        $logContent = file_get_contents($logFile);
        $this->assertNotFalse($logContent);

        $logData = json_decode($logContent, true);
        $this->assertIsArray($logData);
        $this->assertArrayHasKey('close', $logData);

        // Should contain error context
        if (! isset($logData['close']['context'])) {
            return;
        }

        $context = $logData['close']['context'];
        // Error context should have exception information
        $this->assertArrayHasKey('exceptionAsString', $context);
    }

    public function testDevLoggerModuleWithDefaultDirectory(): void
    {
        // Test constructor without parameters (uses sys_get_temp_dir())
        $module = new DevSemanticLoggerModule();
        $module->install(new TestModule());

        $injector = new Injector($module);
        $resource = $injector->getInstance(ResourceInterface::class);

        // Make resource call
        $resourceObject = $resource->get('app://self/simple', ['id' => 'default-dir-test']);
        $this->assertSame(200, $resourceObject->code);

        // Verify log files are created in default temp directory
        $defaultTempDir = sys_get_temp_dir();
        $logFiles = glob($defaultTempDir . '/semantic-dev-*.json');
        $this->assertNotFalse($logFiles);
        $this->assertGreaterThan(0, count($logFiles));

        // Clean up
        foreach ($logFiles as $file) {
            unlink($file);
        }
    }
}
