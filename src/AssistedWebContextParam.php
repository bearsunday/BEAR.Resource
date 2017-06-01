<?php
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

    public function __construct(AbstractWebContextParam $webContextParam)
    {
        $this->webContextParam = $webContextParam;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        unset($varName, $injector);
        $superGlobals = static::$globals ? static::$globals : $GLOBALS;
        $webContextParam = $this->webContextParam;
        $phpWebContext = $superGlobals[$webContextParam::GLOBAL_KEY];

        return isset($phpWebContext[$this->webContextParam->key]) ? $phpWebContext[$this->webContextParam->key] : null;
    }

    public static function setSuperGlobalsOnlyForTestingPurpose(array $globals)
    {
        self::$globals = $globals;
    }
}
