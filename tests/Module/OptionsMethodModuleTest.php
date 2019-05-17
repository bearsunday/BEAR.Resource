<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\OptionsMethods;
use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class OptionsMethodModuleTest extends TestCase
{
    public function testOptionsMethodModule()
    {
        $injector = new Injector(new OptionsMethodModule(new class extends AbstractModule {
            protected function configure()
            {
                $this->bind(OptionsMethods::class);
                $this->bind(Reader::class)->to(AnnotationReader::class);
            }
        }));
        $renderer = $injector->getInstance(RenderInterface::class, 'options');
        $this->assertInstanceOf(OptionsRenderer::class, $renderer);
    }
}
