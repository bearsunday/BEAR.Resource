<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\AbstractModule;

class EmbedResourceModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(Reader::class)->to(AnnotationReader::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptor::class]
        );
    }
}
