<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use Ray\Di\AbstractModule;

/**
 * Provides ResourceInterface and derived bindings
 *
 * The following module is installed:
 *
 * AppName
 *
 * The following module is installed:
 *
 * ResourceClientModule
 * AnnotationModule
 * EmbedResourceModule
 * HttpClientModule
 */
final class ResourceModule extends AbstractModule
{
    /** @param string $appName Application name ex) 'Vendor\Project' */
    public function __construct(
        private string $appName = '',
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->install(new ResourceClientModule());
        $this->install(new EmbedResourceModule());
        $this->install(new HttpClientModule());
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appName);

        // Backward compatibility
        /** @psalm-suppress DeprecatedClass */
        $this->install(new AnnotationModule());
    }
}
