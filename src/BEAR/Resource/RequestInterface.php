<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Request")
 *
 */
interface RequestInterface
{
    /**
     * Constructor
     *
     * @param InvokerInterface $invoker
     *
     * @Inject
     */
    public function __construct(InvokerInterface $invoker);

    /**
     * InvokerInterface resource request
     *
     * @param array $query
     *
     * @return AbstractObject
     */
    public function __invoke(array $query = null);

    /**
     * To Request URI string
     *
     * @return string
     */
    public function toUri();

    /**
     * To Request URI string with request method
     *
     * @return string
     */
    public function toUriWithMethod();
}
