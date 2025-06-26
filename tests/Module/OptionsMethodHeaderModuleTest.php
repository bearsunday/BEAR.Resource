<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\FakeResource;
use BEAR\Resource\Options\InputParamMeta;
use BEAR\Resource\Options\InputParamMetaInterface;
use BEAR\Resource\Options\OptionsMethods;
use BEAR\Resource\Options\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;

class OptionsMethodHeaderModuleTest extends TestCase
{
    public function testOptionsMethodHeaderModule(): void
    {
        $injector = new Injector(new OptionsMethodHeaderModule(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(OptionsMethods::class);
                $this->bind(InputParamMetaInterface::class)->to(InputParamMeta::class);
                $this->bind(Reader::class)->to(AnnotationReader::class);
            }
        }));
        $renderer = $injector->getInstance(RenderInterface::class, 'options');
        assert($renderer instanceof OptionsRenderer);
        $this->assertInstanceOf(OptionsRenderer::class, $renderer);
        $view = $renderer->render(new FakeResource());
        $this->assertSame('', $view);
    }
}
