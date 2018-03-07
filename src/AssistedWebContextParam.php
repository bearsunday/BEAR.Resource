<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;

final class AssistedWebContextParam implements ParamInterface
{
    /**
     * $GLOBALS for testing
     *
     * @var array
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
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        $superGlobals = static::$globals ? static::$globals : $GLOBALS;
        $webContextParam = $this->webContextParam;
        $phpWebContext = $superGlobals[$webContextParam::GLOBAL_KEY];

        if (isset($phpWebContext[$this->webContextParam->key])) {
            return  $phpWebContext[$this->webContextParam->key];
        }

        return $this->defaultParam->__invoke($varName, $query, $injector);
    }

    public static function setSuperGlobalsOnlyForTestingPurpose(array $globals)
    {
        self::$globals = $globals;
    }
}
