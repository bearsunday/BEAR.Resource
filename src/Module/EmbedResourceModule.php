<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;

class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('Doctrine\Common\Annotations\Reader')->to('Doctrine\Common\Annotations\AnnotationReader');
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Resource\Annotation\Embed'),
            ['BEAR\Resource\Interceptor\EmbedInterceptor']
        );
    }
}
