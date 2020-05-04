<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\FakeResource;
use BEAR\Resource\OptionsMethods;
use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class OptionsMethodHeaderModuleTest extends TestCase
{
    public function testOptionsMethodHeaderModule() : void
    {
        $injector = new Injector(new OptionsMethodHeaderModule(new class extends AbstractModule {
            protected function configure()
            {
                $this->bind(OptionsMethods::class);
                $this->bind(Reader::class)->to(AnnotationReader::class);
            }
        }));
        /** @var OptionsRenderer $renderer */
        $renderer = $injector->getInstance(RenderInterface::class, 'options');
        $this->assertInstanceOf(OptionsRenderer::class, $renderer);
        $view = $renderer->render(new FakeResource);
        $this->assertSame('', $view);
    }
}
