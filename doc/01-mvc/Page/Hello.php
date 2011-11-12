<?php
namespace helloWorld\Page;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject as Page,
    BEAR\Resource\Resource;

/**
 * Hello World page resource
 */
class Hello extends Page
{
    /**
     * HTTP headers
     *
     * @var array
     */
    public $headers = ['Content-Type: text/html; charset=UTF-8'];

    /**
     * @var ResourceObject
     */
    protected $greeting;

    /**
     * @param Resource@$resource@Resource Client
     *
     * @Inject
     */
    public function __construct(Resource $resource){
        $this->resource = $resource;
        $this->greeting = $resource->newInstance('app://self/greeting');
    }

    /**
     * @param string $lang laungauage
     *
     * @return ResourceObject
     */
    public function onGet($lang)
    {
        $this['greeting'] = $this->resource->get->object($this->greeting)->withQuery(['lang' => $lang])->eager->request();
        return $this;
    }

    /**
     * @Provide("lang")
     */
    public function provideLang()
    {
        return 'en'; // return $_GET['lang'];
    }
}
