<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ErrorLogLogger;
use BEAR\Resource\LoggerInterface;
use BEAR\Resource\ProdLogger;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class LoggerModuleTest extends TestCase
{
    public function testProdLoggerModule()
    {
        $psrLoggerModule = new class extends AbstractModule {
            protected function configure()
            {
                $this->bind(\Psr\Log\LoggerInterface::class)->to(NullLogger::class);
            }
        };
        $logger = (new Injector(new ProdLoggerModule($psrLoggerModule)))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(ProdLogger::class, $logger);
        $roClass = new class extends ResourceObject {
        };
        $ro = new $roClass;
        $ro->uri = new Uri('app://self/');
        ($logger)($ro);
        $ro->uri->method = 'post';
        ($logger)($ro);
    }

    public function testDevLoggerModule()
    {
        $logger = (new Injector(new ErrorLogLoggerModule))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(ErrorLogLogger::class, $logger);
    }
}
