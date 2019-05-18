<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\DevLogger;
use BEAR\Resource\LoggerInterface;
use BEAR\Resource\ProdLogger;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class LoggerModuleTest extends TestCase
{
    public function testProdLoggerModule()
    {
        $logger = (new Injector(new ProdLoggerModule))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(ProdLogger::class, $logger);
    }

    public function testDevLoggerModule()
    {
        $logger = (new Injector(new DevLoggerModule))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(DevLogger::class, $logger);
    }
}
