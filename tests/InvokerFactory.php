<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\Injector;

final class InvokerFactory
{
    public function __invoke(string $schemaDir = ''): Invoker
    {
        $injector = new Injector();
        return new Invoker(
            new PhpClassInvoker(
                new NamedParameter(
                    new NamedParamMetas($injector),
                    $injector,
                ),
                new ExtraMethodInvoker(
                    new OptionsRenderer(
                        new OptionsMethods(
                            $schemaDir,
                        ),
                    ),
                ),
                new NullLogger(),
            ),
        );
    }
}
