<?php

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;

class FakeRoot extends ResourceObject
{
    public function onGet()
    {
        $this['one'] = 1;
        $this['two'] = new Request(
            new Invoker(new NamedParameter(new ArrayCache, new VoidParameterHandler), new OptionsRenderer(new AnnotationReader)),
            new FakeChild
        );

        return $this;
    }
}
