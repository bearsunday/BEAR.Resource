<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceDirException;

class AppIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppIterator
     */
    private $appIterator;

    protected function setUp()
    {
        $resourceDir = __DIR__ . '/Fake/MyVendor';
        $this->appIterator = new AppIterator($resourceDir);
    }

    public function testForEach()
    {
        foreach ($this->appIterator as $key => $meta) {
            $isValidUri = filter_var($meta->uri, FILTER_VALIDATE_URL);
            $this->assertTrue((bool) $isValidUri);
            $this->assertInstanceOf(Meta::class, $meta);
        }
    }

    public function testResourceDirException()
    {
        $this->setExpectedException(ResourceDirException::class);
        new AppIterator('/invalid');
    }
}
