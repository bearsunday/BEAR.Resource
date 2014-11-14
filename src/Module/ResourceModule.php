<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Named;

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
     *
     * @Inject
     * @Named("appName=app_dir")
     */
    public function __construct($appName)
    {
        $this->appName = $appName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new NamedArgsModule);
        $this->install(new ResourceClientModule($this->appName));
        $this->install(new EmbedResourceModule($this));
        $this->bind('BEAR\Resource\RenderInterface')->to('BEAR\Resource\JsonRenderer');
    }
}
