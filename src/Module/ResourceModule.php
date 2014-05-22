<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;

class ResourceModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $resourceDir;

    /**
     * @param string $appName
     */
    public function __construct($appName, $resourceDir = '')
    {
        $this->appName = $appName;
        $this->resourceDir = $resourceDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new NamedArgsModule);
        $this->install(new ResourceClientModule($this->appName, $this->resourceDir));
//        $this->install(new EmbedResourceModule($this));
    }
}
