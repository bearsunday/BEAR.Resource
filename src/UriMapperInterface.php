<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface UriMapperInterface
{
    /**
     * @param $externalUri "/blog/posts"
     *
     * @return string internal URI
     */
    public function map($externalUri);

    /**
     * @param string $internalUri
     *
     * @return string external URI
     */
    public function reverseMap($internalUri);
}
