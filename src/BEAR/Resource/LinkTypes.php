<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Link type
 */
final class LinkTypes extends \SplQueue
{
    public function enqueue(LinkTypes $link)
    {
        $this->enqueue($link);
    }
}
