<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Attribute;
use BEAR\Resource\Annotation\AbstractResourceLayout;

#[Attribute(Attribute::TARGET_CLASS)]
final class FakeSearchPage extends AbstractResourceLayout
{
    public function embed(): array
    {
        return ['search' => 'page://self/layout/header'];
    }
}
