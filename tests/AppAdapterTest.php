<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use FakeVendor\Sandbox\Module\AppModule;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector;

class AppAdapterTest extends TestCase
{
    /**
     * @var AppAdapter
     */
    private $appAdapter;

    protected function setUp() : void
    {
        $injector = new Injector(new AppModule, $_ENV['TMP_DIR']);
        $this->appAdapter = new AppAdapter($injector, 'FakeVendor\Sandbox');
    }

    public function testGet()
    {
        $index = $this->appAdapter->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $index);
    }

    public function testNotFound()
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->appAdapter->get(new Uri('page://self/__not_found__'));
    }

    public function testGetWithCompiler()
    {
        $injector = $this->getScriptInjector();
        $appAdapter = new AppAdapter($injector, 'FakeVendor\Sandbox');
        $index = $appAdapter->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $index);
    }

    public function testNotFoundWithCompiler()
    {
        $this->expectException(ResourceNotFoundException::class);
        $scriptDir = __DIR__ . '/tmp';
        $compiler = new DiCompiler(new AppModule, $scriptDir);
        $compiler->compile();
        $injector = new ScriptInjector($scriptDir);
        $appAdapter = new AppAdapter($injector, 'FakeVendor\Sandbox');
        $appAdapter->get(new Uri('page://self/__not_found__'));
    }

    /**
     * @return ScriptInjector
     */
    private function getScriptInjector()
    {
        $scriptDir = __DIR__ . '/tmp';
        $compiler = new DiCompiler(new AppModule, $scriptDir);
        $compiler->compile();

        return new ScriptInjector($scriptDir);
    }
}
