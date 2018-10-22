<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use Ray\Di\AbstractModule;

class ResourceModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @param string $appName Application name ex) 'Vendor\Project'
     */
    public function __construct(string $appName)
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
    }
}
