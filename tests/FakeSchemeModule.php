<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
