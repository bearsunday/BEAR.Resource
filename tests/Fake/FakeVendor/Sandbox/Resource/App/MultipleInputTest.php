<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Class\SearchCriteria;
use FakeVendor\Sandbox\Resource\App\Class\Pager;

class MultipleInputTest extends ResourceObject
{
    public function onGet(
        #[Input] SearchCriteria $criteria,
        #[Input] Pager $pager,
    ): static {
        $this->body = [
            'search' => [
                'query' => $criteria->query,
                'category' => $criteria->category,
            ],
            'pagination' => [
                'page' => $pager->page,
                'limit' => $pager->limit,
                'offset' => $pager->getOffset(),
            ],
        ];
        
        return $this;
    }
}