<?php
namespace helloWorld\Page;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject as Page,
    BEAR\Resource\Resource;

/**
 * Hello World page resource
 */
class HelloAop extends Hello
{
    /**
     * @param Resource $resource
     *
     * @Inject
     */
    public function __construct(Resource $resource){
        $this->resource = $resource;
        $this->greeting = $resource->newInstance('app://self/greeting/aop');
    }
}
