<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\EmbedInterceptor;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\AbstractModule;

class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(Reader::class)->to(AnnotationReader::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Embed::class),
            [EmbedInterceptor::class]
        );
    }
}
