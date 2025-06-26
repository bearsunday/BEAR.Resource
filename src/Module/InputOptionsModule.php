<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Options\InputParamMeta;
use BEAR\Resource\Options\InputParamMetaInterface;
use Ray\Di\AbstractModule;

/**
 * Module for Input attribute OPTIONS support
 */
final class InputOptionsModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(InputParamMetaInterface::class)->to(InputParamMeta::class);
    }
}
