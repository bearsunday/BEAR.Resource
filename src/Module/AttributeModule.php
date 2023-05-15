<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Ray\Di\AbstractModule;

/** @codeCoverageIgnore */
final class AttributeModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(Reader::class)->to(AttributeReader::class);
    }
}
