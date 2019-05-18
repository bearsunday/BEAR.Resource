<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\AbstractModule;

final class LggerModule extends AbstractModule
{
    const LOG_LEVEL_PROD = 0;

    const LOG_LEVEL_DEV = 1;

    const LOG_LEVEL_DEV_RESULT = 2;

    /**
     * @var string
     */
    private $level;

    /**
     * @var AbstractModule
     */
    private $module;

    public function __construct(string $level, AbstractModule $module)
    {
        $this->level = $level;
        $this->module = $module;
        parent::__construct($module);
    }

    protected function configure()
    {
        if ($this->level === self::LOG_LEVEL_PROD) {
            $this->bind(LoggerInterface::class)->to(ProdLogger::class);
        }
        if ($this->level === self::LOG_LEVEL_DEV) {
            $this->bind(LoggerInterface::class)->to(DevZeroLogger::class);
        }
        $this->bind(LoggerInterface::class)->to(DevOneLogger::class);
    }
}
