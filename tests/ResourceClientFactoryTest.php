<?php

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;

class ResourceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $resource = (new ResourceClientFactory)->newClient($_ENV['TMP_DIR'], 'FakeVendor\Sandbox');
        $this->assertInstanceOf(Resource::class, $resource);
    }
}
