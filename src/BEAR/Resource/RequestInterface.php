<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource client
 *
 *
 * @ImplementedBy("BEAR\Resource\Request")
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
     * Set query
     *
     * @param array $query
     *
     * @return self
     */
    public function withQuery(array $query);

    /**
     * Add(merge) query
     *
     * @param array $query
     *
     * @return self
     */
    public function addQuery(array $query);

    /**
     * InvokerInterface resource request
     *
     * @param array $query
     *
     * @return ResourceObject
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

    /**
     * Return request hash
     *
     * @return string
     */
    public function hash();
}
