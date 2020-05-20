<?php

declare (strict_types=1);
namespace FakeVendor\Sandbox\Resource\App\Weave;

use Ray\Aop\WeavedInterface;
use Ray\Aop\ReflectiveMethodInvocation as Invocation;
use BEAR\Resource\Annotation\FakeLog;
use BEAR\Resource\ResourceObject;
class Book_aYB2IYA extends \FakeVendor\Sandbox\Resource\App\Weave\Book implements WeavedInterface
{
    public $bind;
    public $bindings = [];
    public $methodAnnotations = 'a:2:{s:5:"onGet";a:1:{i:0;O:32:"BEAR\\Resource\\Annotation\\FakeLog":0:{}}s:11:"setRenderer";a:1:{i:0;O:16:"Ray\\Di\\Di\\Inject":1:{s:8:"optional";b:1;}}}';
    public $classAnnotations = 'a:0:{}';
    private $isAspect = true;
    /**
     * @FakeLog
     */
    public function onGet(int $id)
    {
        if (!$this->isAspect) {
            $this->isAspect = true;
            return call_user_func_array([$this, 'parent::' . __FUNCTION__], func_get_args());
        }
        $this->isAspect = false;
        $result = (new Invocation($this, __FUNCTION__, func_get_args(), $this->bindings[__FUNCTION__]))->proceed();
        $this->isAspect = true;
        return $result;
    }
}
