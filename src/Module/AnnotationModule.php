<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;
use Ray\Di\AbstractModule;

class AnnotationModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(Reader::class)->toConstructor(CachedReader::class,[
            'reader' => 'base_reader'
        ]);
        $this->bind(Reader::class)->annotatedWith('base_reader')->toConstructor(DualReader::class, [
            'annotationReader' => 'annotation',
            'attributeReader' => 'attribute',
        ]);
        $this->bind(Reader::class)->annotatedWith('annotation')->to(AnnotationReader::class);
        $this->bind(Reader::class)->annotatedWith('attribute')->to(AttributeReader::class);
    }
}
