<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;
use Ray\Di\InjectorInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;

use function assert;
use function is_string;

/**
 * @psalm-import-type Query from Types
 * @psalm-import-type SuperGlobalsMap from Types
 */
final class AssistedWebContextParam implements ParamInterface
{
    /**
     * $GLOBALS for testing
     *
     * @var SuperGlobalsMap
     */
    private static array $globals = [];

    /** @psalm-pure */
    public function __construct(
        private readonly AbstractWebContextParam $webContextParam,
        private readonly ParamInterface $defaultParam,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-taint-source input
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        $superGlobals = static::$globals ?: $GLOBALS;
        /** @var SuperGlobalsMap $superGlobals */
        $webContextParam = $this->webContextParam;
        assert(is_string($webContextParam::GLOBAL_KEY));
        /** @psalm-suppress MixedArrayOffset */
        $phpWebContext = $superGlobals[$webContextParam::GLOBAL_KEY];

        return $phpWebContext[$this->webContextParam->key] ?? ($this->defaultParam)($varName, $query, $injector);
    }

    /**
     * @param SuperGlobalsMap $globals
     *
     * @psalm-capabilities write-globals
     */
    public static function setSuperGlobalsOnlyForTestingPurpose(array $globals): void
    {
        self::$globals = $globals;
    }
}
