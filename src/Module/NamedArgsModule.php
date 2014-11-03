<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class NamedArgsModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Resource\NamedArgsInterface')->to('BEAR\Resource\NamedArgs')->in(Scope::SINGLETON);
    }
}
