<?php

namespace BEAR\Resource;

use Doctrine\Common\Cache\ArrayCache;

class FakeRoot extends ResourceObject
{
    public function onGet()
    {
        $this['one'] = 1;
        $this['two'] = new Request(
            new Invoker(new NamedParameter(new ArrayCache, new VoidParamHandler)),
            new FakeChild
        );

        return $this;
    }
}
