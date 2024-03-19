<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ResourceObject;
use Ray\Di\AbstractModule;

/**
 * Bind resource object for compile
 */
class ResourceObjectModule extends AbstractModule
{
    /** @param iterable<class-string<ResourceObject>> $resourceObjects */
    public function __construct(
        private readonly iterable $resourceObjects,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        foreach ($this->resourceObjects as $ro) {
            $this->bind($ro);
        }
    }
}
