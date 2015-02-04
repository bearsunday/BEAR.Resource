<?php

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\EmptyModule;
use Ray\Di\Injector;

class ResourceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceRepository
     */
    private $resourceRepo;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var ResourceInterface
     */
    private $ro;

    protected function setUp()
    {
        parent::setUp();
        $this->resourceRepo = new ResourceRepository(new ArrayCache);
        $resource = (new Injector(new ResourceModule('FakeVendor\Sandbox'), $_ENV['TMP_DIR']))->getInstance(ResourceInterface::class);
        $this->uri = new Uri('app://self/author', ['id' => 1]);
        $this->ro = $resource->get->uri($this->uri)->eager->request();
    }

    public function testSave()
    {
        $result = $this->resourceRepo->save($this->ro);
        $this->assertTrue($result);

        return $this->resourceRepo;
    }

    /**
     * @depends testSave
     */
    public function testContains(ResourceRepository $repo)
    {
        $result = $repo->contains($this->uri);
        $this->assertTrue($result);
    }

    /**
     * @depends testSave
     */
    public function testFetch(ResourceRepository $repo)
    {
        $result = $repo->fetch($this->uri);
        $this->assertInstanceOf(ResourceObject::class, $result);
        $this->assertSame((string) $this->uri, (string) $result->uri);
    }

    /**
     * @depends testSave
     */
    public function testDelete(ResourceRepository $repo)
    {
        $result = $repo->delete($this->uri);
        $this->assertTrue($result);
        $result = $repo->contains($this->uri);
        $this->assertFalse($result);
    }
}
