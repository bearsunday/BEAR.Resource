<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;
use Ray\Di\AbstractModule;

/**
 * Provides DualReader and derived bindings
 *
 * The following module is installed:
 *
 * Reader-annotation
 * Reader-attribute
 *
 * @deprecated See https://github.com/bearsunday/BEAR.Resource/wiki/Doctrine-annotation-deprecation-notice
 */
final class AnnotationModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(Reader::class)->toConstructor(DualReader::class, [
            'annotationReader' => 'annotation',
            'attributeReader' => 'attribute',
        ]);
        $this->bind(Reader::class)->annotatedWith('annotation')->to(AnnotationReader::class);
        $this->bind(Reader::class)->annotatedWith('attribute')->to(AttributeReader::class);
    }
}
