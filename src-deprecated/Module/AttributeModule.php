<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Ray\Di\AbstractModule;

/** @codeCoverageIgnore */

/**
 * @deprecated See https://github.com/bearsunday/BEAR.Resource/wiki/Doctrine-annotation-deprecation-notice
 */
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
