<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for hyper reference
 */
interface HrefInterface
{
    /**
     * Get hyper reference URI
     *
     * @param                $rel
     * @param AbstractObject $ro
     *
     * @return mixed
     */
    public function href($rel, AbstractObject $ro);
}
