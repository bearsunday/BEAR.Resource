<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;

class FakeFreeze extends ResourceObject
{
    public $code = 201;
    public $body = ['php' => '7'];

    public $closure;

    public function __construct()
    {
        $this->closure = function () {
        };
        $module = new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'));
        /* @var $resource ResourceInterface */
        $resource = (new Injector($module, $_ENV['TMP_DIR']))->getInstance(ResourceInterface::class);
        $this['user'] = $resource->get->uri('app://self/author')->withQuery(['id' => 1])->eager->request();
    }
}
