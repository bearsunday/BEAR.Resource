<?php

declare (strict_types=1);
namespace FakeVendor\Sandbox\Resource\App\Href;

use Ray\Aop\WeavedInterface;
use Ray\Aop\ReflectiveMethodInvocation as Invocation;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
class Hasembed_IggGcyM extends \FakeVendor\Sandbox\Resource\App\Href\Hasembed implements WeavedInterface
{
    public $bind;
    public $bindings = [];
    public $methodAnnotations = 'a:2:{s:5:"onGet";a:2:{i:0;O:30:"BEAR\\Resource\\Annotation\\Embed":2:{s:3:"rel";s:5:"bird1";s:3:"src";s:22:"app://self/bird/canary";}i:1;O:29:"BEAR\\Resource\\Annotation\\Link":5:{s:3:"rel";s:4:"next";s:4:"href";s:30:"app://self/href/target?id={id}";s:6:"method";s:3:"get";s:5:"title";s:0:"";s:5:"crawl";s:0:"";}}s:11:"setRenderer";a:1:{i:0;O:16:"Ray\\Di\\Di\\Inject":1:{s:8:"optional";b:1;}}}';
    public $classAnnotations = 'a:0:{}';
    private $isAspect = true;
    /**
     * @Embed(rel="bird1", src="app://self/bird/canary")
     * @Link(rel="next", href="app://self/href/target?id={id}")
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
