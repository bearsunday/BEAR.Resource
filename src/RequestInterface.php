<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * @property AbstractRequest $eager
 * @property AbstractRequest $lazy
 */
interface RequestInterface
{
    /**
     * Set query
     *
     * @param array $query
     *
     * @return $this
     */
    public function withQuery(array $query);

    /**
     * Add(merge) query
     *
     * @param array $query
     *
     * @return $this
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
