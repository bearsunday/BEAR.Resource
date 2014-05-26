<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Adapter\App as AppAdapter;
use BEAR\Resource\Adapter\Http as HttpAdapter;
use BEAR\Resource\Exception\AppName;
use BEAR\Resource\SchemeCollection;
use Ray\Di\ProviderInterface as Provide;
use Ray\Di\InstanceInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * SchemeCollection provider
 */
class SchemeCollectionProvider implements Provide
{
    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string
     */
    protected $resourceDir;

    /**
     * @var InstanceInterface
     */
    protected $injector;

    /**
     * @param string $appName
     *
     * @return void
     *
     * @throws \BEAR\Resource\Exception\AppName
     * @Inject
     * @Named("appName=app_name,resourceDir=resource_dir")
     */
    public function setAppName($appName, $resourceDir)
    {
        if (! is_string($appName)) {
            throw new AppName($appName);
        }
        $this->appName = $appName;
        $this->resourceDir = $resourceDir;
    }

    /**
     * @param InstanceInterface $injector
     *
     * @Inject
     */
    public function setInjector(InstanceInterface $injector)
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
        $pageAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\Page', $this->resourceDir . '/Page');
        $appAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\App', $this->resourceDir . '/App');
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);
        $schemeCollection->scheme('http')->host('*')->toAdapter(new HttpAdapter);

        return $schemeCollection;
    }
}
