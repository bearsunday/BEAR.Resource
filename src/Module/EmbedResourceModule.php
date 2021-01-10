<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;
use Ray\Di\AbstractModule;

class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(Reader::class)->toConstructor(DualReader::class, [
            'annotationReader' => 'annotation',
            'attributeReader' => 'attribute',
        ]);
        $this->bind(Reader::class)->annotatedWith('annotation')->to(AnnotationReader::class);
        $this->bind(Reader::class)->annotatedWith('attribute')->to(AttributeReader::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptor::class]
        );
    }
}
