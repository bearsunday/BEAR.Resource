<?php

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;

class ResourceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $resource = (new ResourceClientFactory)->newInstance('FakeVendor\Sandbox', new AnnotationReader);
        $this->assertInstanceOf(Resource::class, $resource);
    }
}
