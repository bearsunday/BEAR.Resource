<?php

namespace Module;

use BEAR\Resource\Module\ImportAppModule;
use BEAR\Resource\ResourceInterface;
use FakeVendor\Sandbox\AppModule;
use Ray\Di\Injector;

class ImportAppModuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testConfigure()
    {
        $module = new AppModule;
        $importConfig = [
            'blog' => ['FakeVendor\Blog', 'app']
        ];
        $module->override(new ImportAppModule($importConfig));
        $resource = (new Injector($module))->getInstance(ResourceInterface::class);
        // request
        $news = $resource
            ->get
            ->uri('app://self/news')
            ->withQuery(['date' => 'today'])
            ->request();
        $expect = <<<EOT
{"weather":{"today":"the weather of today is sunny"},"headline":"40th anniversary of Rubik's Cube invention.","sports":"Pieter Weening wins Giro d'Italia.","user":{"id":2,"name":"Aramis","age":16,"blog_id":12}}
EOT;
        $this->assertSame($expect, (string) $news);

        $news = $resource
            ->get
            ->uri('app://blog/news')
            ->withQuery(['date' => 'today'])
            ->request();
        $expect = <<<EOT
{"weather":{"today":"the weather of today is sunny"},"technology":"Microsoft to stop producing Windows versions","user":{"id":3,"name":"Porthos","age":17,"blog_id":0}}
EOT;
        $this->assertSame($expect, (string) $news);
    }
}
