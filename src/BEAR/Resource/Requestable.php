<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @ImplementedBy("BEAR\Resource\Request")
 *
 */
interface Requestable
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
     */
    public function __invoke(array $query = null);
}
