<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\AbstractModule;

class FakeSchemeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(SchemeCollectionInterface::class)->toProvider(FakeSchemeCollectionProvider::class);
    }
}
