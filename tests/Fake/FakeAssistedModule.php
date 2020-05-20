<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\AbstractModule;

class FakeAssistedModule extends AbstractModule
{
    protected function configure() : void
    {
        $this->bind()->annotatedWith('login_id')->toInstance('assisted01');
    }
}
