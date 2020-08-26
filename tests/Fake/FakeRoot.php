<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\Injector;

class FakeRoot extends ResourceObject
{
    public function onGet()
    {
        $fakeChild = (new FakeRo())(new FakeChild());
        $this->body = [
            'one' => 1,
            'two' => new Request((new InvokerFactory())(), $fakeChild)
        ];

        return $this;
    }
}
