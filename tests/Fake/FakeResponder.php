<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

class FakeResponder implements TransferInterface
{
    public $class;

    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $ro, array $server)
    {
        // transfer resource object to external boundary (HTTP / File ...)
        unset($server);
        $this->class = get_class($ro);
    }
}
