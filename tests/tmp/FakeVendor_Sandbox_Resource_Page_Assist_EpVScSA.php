<?php

declare (strict_types=1);
namespace FakeVendor\Sandbox\Resource\Page;

use Ray\Aop\WeavedInterface;
use Ray\Aop\ReflectiveMethodInvocation as Invocation;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;
class Assist_EpVScSA extends \FakeVendor\Sandbox\Resource\Page\Assist implements WeavedInterface
{
    public $bind;
    public $bindings = [];
    public $methodAnnotations = 'a:2:{s:5:"onGet";a:2:{i:0;O:18:"Ray\\Di\\Di\\Assisted":1:{s:6:"values";a:1:{i:0;s:8:"login_id";}}i:1;O:15:"Ray\\Di\\Di\\Named":1:{s:5:"value";s:17:"login_id=login_id";}}s:11:"setRenderer";a:1:{i:0;O:16:"Ray\\Di\\Di\\Inject":1:{s:8:"optional";b:1;}}}';
    public $classAnnotations = 'a:0:{}';
    private $isAspect = true;
    /**
     * @Assisted({"login_id"})
     * @Named("login_id=login_id")
     */
    public function onGet(string $login_id = null)
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
