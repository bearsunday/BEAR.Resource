<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Module;

use BEAR\Resource\Module\ResourceModule;
use Ray\Di\AbstractModule;
use Override;

final class TestModule extends AbstractModule
{
    #[Override]
    protected function configure(): void
    {
        $this->install(new ResourceModule('BEAR\Resource\Fake\SemanticLogger'));
    }
}