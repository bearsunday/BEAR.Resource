<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use BEAR\Resource\Module\NamedArgsModule;

class EmbedResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new NamedArgsModule);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Resource\Annotation\Embed'),
            [$this->requestInjection('BEAR\Resource\Interceptor\EmbedInterceptor')]
        );
    }
}
