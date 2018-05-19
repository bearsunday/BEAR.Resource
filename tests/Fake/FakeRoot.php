<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Injector;

class FakeRoot extends ResourceObject
{
    public function onGet()
    {
        $this['one'] = 1;
        $this['two'] = new Request(
            new Invoker(new NamedParameter(new NamedParamMetas(new ArrayCache, new AnnotationReader), new Injector), new OptionsRenderer(new OptionsMethods(new AnnotationReader))),
            new FakeChild
        );

        return $this;
    }
}
