<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Attribute;
use BEAR\Resource\Annotation\AbstractEmbedPage;

#[Attribute(Attribute::TARGET_CLASS)]
final class FakeContentPage extends AbstractEmbedPage
{
    public function embed(): array
    {
        return [
            'header' => 'page://self/embed-page/header',
            'footer' => '/embed-page/footer{?id,lang}',
        ];
    }
}
