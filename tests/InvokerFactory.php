<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;
use Ray\Di\Injector;

final class InvokerFactory
{
    public function __invoke(string $schemaDir = ''): Invoker
    {
        $reader = new DualReader(new AnnotationReader(), new AttributeReader());

        return new Invoker(
            new NamedParameter(
                new NamedParamMetas(
                    new ArrayCache(),
                    $reader
                ),
                new Injector()
            ),
            new ExtraMethodInvoker(
                new OptionsRenderer(
                    new OptionsMethods(
                        $reader,
                        $schemaDir
                    )
                )
            ),
            new NullLogger()
        );
    }
}
