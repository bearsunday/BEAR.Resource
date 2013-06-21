<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Adapter\App as AppAdapter;
use BEAR\Resource\Adapter\Http as HttpAdapter;
use BEAR\Resource\SchemeCollection;
use Ray\Di\ProviderInterface as Provide;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * SchemeCollection provider
 */
class SchemeCollectionProvider implements Provide
{
    private $appName = 'Sandbox';

    /**
     * @param string $appName
     *
     * @Inject(optional=true)
     * @Named("app_name")
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @param InjectorInterface $injector
     *
     * @Inject
     */
    public function setInjector(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * Return instance
     *
     * @return SchemeCollection
     */
    public function get()
    {
        $schemeCollection = new SchemeCollection;
        $pageAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\Page');
        $appAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\App');
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);
        $schemeCollection->scheme('http')->host('*')->toAdapter(new HttpAdapter);

        return $schemeCollection;
    }
}
