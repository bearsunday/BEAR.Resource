<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Injector;

final class InvokerFactory
{
    public function __invoke(string $schemaDir = '')
    {
        $reader = new AnnotationReader;

        return new Invoker(
            new NamedParameter(
                new NamedParamMetas(
                    new ArrayCache,
                    $reader
                ),
                new Injector
            ),
            new ExtraMethodInvoker(
                new OptionsRenderer(
                    new OptionsMethods(
                        $reader,
                        $schemaDir
                    )
                )
            )
        );
    }
}
