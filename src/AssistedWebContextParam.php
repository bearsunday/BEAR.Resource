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
     * @var AbstractWebContextParam
     */
    private $webContextParam;

    /**
     * @var array
     */
    private $globals;

    public function __construct(AbstractWebContextParam $webContextParam, array $globals = null)
    {
        $this->webContextParam = $webContextParam;
        $this->globals = $globals;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        unset($varName, $injector);
        $superGlobals = $this->globals ? $this->globals : $GLOBALS;
        $webContextParam = $this->webContextParam;
        $phpWebContext = $superGlobals[$webContextParam::GLOBAL_KEY];

        return isset($phpWebContext[$this->webContextParam->key]) ? $phpWebContext[$this->webContextParam->key] : null;
    }
}
