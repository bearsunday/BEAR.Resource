<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Attribute;
use BEAR\Resource\Annotation\AbstractResourceLayout;

#[Attribute(Attribute::TARGET_CLASS)]
final class FakeContentPage extends AbstractResourceLayout
{
    public function embed(): array
    {
        return [
            'header' => 'page://self/layout/header',
            'footer' => '/layout/footer{?id,lang}',
        ];
    }
}
