<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\DevLogger;
use BEAR\Resource\FakeResource;
use BEAR\Resource\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class DevLoggerModuleTest extends TestCase
{
    /**
     * @covers \BEAR\Resource\DevLogger
     * @covers \BEAR\Resource\Module\DevLoggerModule
     */
    public function testDevLoggerModule(): void
    {
        $psrLoggerModule = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(\Psr\Log\LoggerInterface::class)->to(NullLogger::class);
            }
        };
        $module = new DevLoggerModule();
        $module->install($psrLoggerModule);
        $logger = (new Injector($module))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(DevLogger::class, $logger);
        $logger(new FakeResource());
    }
}
