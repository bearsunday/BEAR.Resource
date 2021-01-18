<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Injector;
use Ray\ServiceLocator\ServiceLocator;

final class InvokerFactory
{
    public function __invoke(string $schemaDir = ''): Invoker
    {
        $reader = ServiceLocator::getReader();

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
