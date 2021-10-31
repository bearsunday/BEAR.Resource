<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use Ray\Di\AbstractModule;

final class ResourceModule extends AbstractModule
{
    /** @var string */
    private $appName;

    /**
     * @param string $appName Application name ex) 'Vendor\Project'
     */
    public function __construct(string $appName = '')
    {
        $this->appName = $appName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->install(new ResourceClientModule());
        $this->install(new AnnotationModule());
        $this->install(new EmbedResourceModule());
        $this->install(new HttpClientModule());
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appName);
    }
}
