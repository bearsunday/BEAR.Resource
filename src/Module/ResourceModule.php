<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\OptionsMethods;
use Ray\Di\AbstractModule;

class ResourceModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @param string $appName
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
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appName);
        $this->install(new ResourceClientModule);
        $this->install(new EmbedResourceModule);
        $this->bind(OptionsMethods::class);
    }
}
