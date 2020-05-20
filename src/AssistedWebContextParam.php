<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;

final class AssistedWebContextParam implements ParamInterface
{
    /**
     * $GLOBALS for testing
     *
     * @var array<string, array<string, string>>
     */
    private static $globals = [];

    /**
     * @var AbstractWebContextParam
     */
    private $webContextParam;

    /**
     * @var ParamInterface
     */
    private $defaultParam;

    public function __construct(AbstractWebContextParam $webContextParam, ParamInterface $defaultParam)
    {
        $this->webContextParam = $webContextParam;
        $this->defaultParam = $defaultParam;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        $superGlobals = static::$globals ? static::$globals : $GLOBALS;
        $webContextParam = $this->webContextParam;
        $phpWebContext = $superGlobals[$webContextParam::GLOBAL_KEY];

        if (isset($phpWebContext[$this->webContextParam->key])) {
            return  $phpWebContext[$this->webContextParam->key];
        }

        return ($this->defaultParam)($varName, $query, $injector);
    }

    /**
     * @param array<string, array<string, string>> $globals
     */
    public static function setSuperGlobalsOnlyForTestingPurpose(array $globals) : void
    {
        self::$globals = $globals;
    }
}
