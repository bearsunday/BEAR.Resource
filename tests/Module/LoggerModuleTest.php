<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ErrorLogLogger;
use BEAR\Resource\FakeResource;
use BEAR\Resource\LoggerInterface;
use BEAR\Resource\ProdLogger;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function file_get_contents;
use function ini_get;
use function ini_set;

class LoggerModuleTest extends TestCase
{
    public function testProdLoggerModule(): void
    {
        $psrLoggerModule = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(\Psr\Log\LoggerInterface::class)->to(NullLogger::class);
            }
        };
        $logger = (new Injector(new ProdLoggerModule($psrLoggerModule)))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(ProdLogger::class, $logger);
        $roClass = new class extends ResourceObject {
        };
        $ro = new $roClass();
        $ro->uri = new Uri('app://self/');
        ($logger)($ro);
        $ro->uri->method = 'post';
        ($logger)($ro);
    }

    public function testDevLoggerModule(): void
    {
        $logger = (new Injector(new ErrorLogLoggerModule()))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(ErrorLogLogger::class, $logger);
        assert($logger instanceof ErrorLogLogger);
        $errorLog = (string) ini_get('error_log');
        $errorLogText = __DIR__ . '/tmp/error_log.txt';
        ini_set('error_log', $errorLogText);
        ($logger)(new FakeResource());
        ini_set('error_log', $errorLog);
        $this->assertStringContainsString('get app://self/index', (string) file_get_contents($errorLogText));
    }
}
