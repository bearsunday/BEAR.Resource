<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Options\InputParamMeta;
use Ray\Di\Injector;

final class InvokerFactory
{
    public function __invoke(string $schemaDir = ''): Invoker
    {
        return new Invoker(
            new PhpClassInvoker(
                new NamedParameter(
                    new NamedParamMetas(),
                    new Injector(),
                ),
                new ExtraMethodInvoker(
                    new OptionsRenderer(
                        new OptionsMethods(
                            new InputParamMeta(),
                            $schemaDir,
                        ),
                    ),
                ),
                new NullLogger(),
            ),
        );
    }
}
