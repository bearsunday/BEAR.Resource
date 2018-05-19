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
        $fakeChild = new FakeChild;
        $fakeChild->uri = new Uri('app://self/dummy');
        $this->body = [
            'one' => 1,
            'two' => new Request(
                new Invoker(
                    new NamedParameter(
                        new NamedParamMetas(
                            new ArrayCache,
                            new AnnotationReader
                        ),
                        new Injector
                    ),
                    new OptionsRenderer(
                        new OptionsMethods(
                            new AnnotationReader
                        )
                    )
                ),
            $fakeChild
            )
        ];

        return $this;
    }
}
