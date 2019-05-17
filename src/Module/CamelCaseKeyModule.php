<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Interceptor\CamelCaseKeyInterceptor;
use BEAR\Resource\ResourceObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\AbstractModule;

class CamelCaseKeyModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(Reader::class)->to(AnnotationReader::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->startsWith('toString'),
            [CamelCaseKeyInterceptor::class]
        );
    }
}
