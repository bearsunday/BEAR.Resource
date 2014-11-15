<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\AppAdapter;
use BEAR\Resource\Exception\AppName;
use BEAR\Resource\SchemeCollection;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

class SchemeCollectionProvider implements ProviderInterface
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
     * @var InjectorInterface
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
        $pageAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\Page', $this->resourceDir . '/Page');
        $appAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\App', $this->resourceDir . '/App');
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);

        return $schemeCollection;
    }
}
