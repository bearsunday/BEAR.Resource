<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;

class FakeJsonModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith('enable_fake_json')->toInstance(true);
    }
}
