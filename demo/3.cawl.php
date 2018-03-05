<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;
namespace MyVendor\Sandbox\Resource\App {
    require dirname(__DIR__) . '/vendor/autoload.php';

    use BEAR\Resource\Annotation\Link;
    use BEAR\Resource\ResourceObject;

    class Author extends ResourceObject
    {
        protected $users = [
            ['id' => 1, 'name' => 'Athos'],
            ['id' => 2, 'name' => 'Aramis'],
            ['id' => 3, 'name' => 'Porthos']
        ];

        /**
         * @Link(crawl="tree", rel="post", href="app://self/post?author_id={id}")
         */
        public function onGet($id = null)
        {
            return $id === null ? $this->users : $this->users[$id];
        }
    }

    class Meta extends ResourceObject
    {
        use SelectTrait;

        private $repo = [
            [
                'id' => '1',
                'post_id' => '1',
                'data' => 'meta 1',
            ],
            [
                'id' => '2',
                'post_id' => '2',
                'data' => 'meta 2',
            ],
            [
                'id' => '3',
                'post_id' => '3',
                'data' => 'meta 3',
            ],
            [
                'id' => '4',
                'post_id' => '4',
                'data' => 'meta 4',
            ],
            [
                'id' => '5',
                'post_id' => '5',
                'data' => 'meta 5',
            ],
        ];

        /**
         * @param id
         *
         * @return array
         */
        public function onGet($post_id)
        {
            return $this->select('id', $post_id);
        }
    }

    class Post extends ResourceObject
    {
        use SelectTrait;

        private $repo = [
            [
                'id' => '1',
                'author_id' => '1',
                'body' => 'Anna post #1',
            ],
            [
                'id' => '2',
                'author_id' => '1',
                'body' => 'Anna post #2',
            ],
            [
                'id' => '3',
                'author_id' => '1',
                'body' => 'Anna post #3',
            ],
            [
                'id' => '4',
                'author_id' => '2',
                'body' => 'Clara post #1',
            ],
            [
                'id' => '5',
                'author_id' => '2',
                'body' => 'Clara post #2',
            ],
        ];

        /**
         * @Link(crawl="tree", rel="meta", href="app://self/meta?post_id={id}", method="get")
         * @Link(crawl="tree", rel="tag",  href="app://self/tag?post_id={id}",  method="get")
         */
        public function onGet($author_id)
        {
            $posts = $this->select('author_id', $author_id);

            return $posts;
        }
    }

    class Tag extends ResourceObject
    {
        use SelectTrait;

        private $repo = [
            [
                'id' => '1',
                'post_id' => '1',
                'tag_id' => '1',
            ],
            [
                'id' => '2',
                'post_id' => '1',
                'tag_id' => '2',
            ],
            [
                'id' => '3',
                'post_id' => '2',
                'tag_id' => '2',
            ],
            [
                'id' => '4',
                'post_id' => '2',
                'tag_id' => '3',
            ],
            [
                'id' => '5',
                'post_id' => '3',
                'tag_id' => '3',
            ],
            [
                'id' => '6',
                'post_id' => '3',
                'tag_id' => '1',
            ],
            [
                'id' => '7',
                'post_id' => '4',
                'tag_id' => '1',
            ],
            [
                'id' => '8',
                'post_id' => '4',
                'tag_id' => '2',
            ],
            [
                'id' => '9',
                'post_id' => '4',
                'tag_id' => '3',
            ],
            [
                'id' => '10',
                'post_id' => '5',
                'tag_id' => '2',
            ],
        ];

        /**
         * @Link(crawl="tree", rel="tag_name",  href="app://self/tag/name?tag_id={tag_id}",  method="get")
         * @Link(crawl="another_tree", rel="xxx",  href="app://path/to/another/resource",  method="get")
         */
        public function onGet($post_id)
        {
            return $this->select('post_id', $post_id);
        }
    }

    trait SelectTrait
    {
        public function select($key, $id)
        {
            $result = [];
            foreach ($this->repo as $item) {
                if ($item[$key] == $id) {
                    $result[] = $item;
                }
            }

            return $result;
        }
    }
}
namespace MyVendor\Sandbox\Resource\App\Tag {
    use BEAR\Resource\ResourceObject;
    use MyVendor\Sandbox\Resource\App\SelectTrait;

    class Name extends ResourceObject
    {
        use SelectTrait;

        private $repo = [
            [
                'id' => '1',
                'name' => 'zim',
            ],
            [
                'id' => '2',
                'name' => 'dib',
            ],
            [
                'id' => '3',
                'name' => 'gir',
            ],
        ];

        public function onGet($tag_id)
        {
            return $this->select('id', $tag_id);
        }
    }
}
namespace Main {
    use BEAR\Resource\Module\HalModule;
    use BEAR\Resource\Module\ResourceModule;
    use BEAR\Resource\ResourceInterface;
    use Ray\Di\Injector;

    /** @var ResourceInterface $resource */
    $resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Sandbox'))))->getInstance(ResourceInterface::class);
    $author = $resource->get->uri('app://self/author')->linkCrawl('tree')();
    echo json_encode($author->body, JSON_PRETTY_PRINT) . PHP_EOL;
}
//[
//    {
//        "id": 1,
//        "name": "Athos",
//        "post": [
//            {
//                "id": "1",
//                "author_id": "1",
//                "body": "Anna post #1",
//                "meta": [
//                    {
//                        "id": "1",
//                        "post_id": "1",
//                        "data": "meta 1"
//                    }
//                ],
//                "tag": [
//                    {
//                        "id": "1",
//                        "post_id": "1",
//                        "tag_id": "1",
//                        "tag_name": [
//                            {
//                                "id": "1",
//                                "name": "zim"
//                            }
//                        ]
//                    },
//                    {
//                        "id": "2",
//                        "post_id": "1",
//                        "tag_id": "2",
//                        "tag_name": [
//                            {
//                                "id": "2",
//                                "name": "dib"
//                            }
//                        ]
//                    }
//                ]
//            },
//            {
//                "id": "2",
//                "author_id": "1",
//                "body": "Anna post #2",
//                "meta": [
//                    {
//                        "id": "2",
//                        "post_id": "2",
//                        "data": "meta 2"
//                    }
//                ],
//                "tag": [
//                    {
//                        "id": "3",
//                        "post_id": "2",
//                        "tag_id": "2",
//                        "tag_name": [
//                            {
//                                "id": "2",
//                                "name": "dib"
//                            }
//                        ]
//                    },
//                    {
//                        "id": "4",
//                        "post_id": "2",
//                        "tag_id": "3",
//                        "tag_name": [
//                            {
//                                "id": "3",
//                                "name": "gir"
//                            }
//                        ]
//                    }
//                ]
//            },
//            {
//                "id": "3",
//                "author_id": "1",
//                "body": "Anna post #3",
//                "meta": [
//                    {
//                        "id": "3",
//                        "post_id": "3",
//                        "data": "meta 3"
//                    }
//                ],
//                "tag": [
//                    {
//                        "id": "5",
//                        "post_id": "3",
//                        "tag_id": "3",
//                        "tag_name": [
//                            {
//                                "id": "3",
//                                "name": "gir"
//                            }
//                        ]
//                    },
//                    {
//                        "id": "6",
//                        "post_id": "3",
//                        "tag_id": "1",
//                        "tag_name": [
//                            {
//                                "id": "1",
//                                "name": "zim"
//                            }
//                        ]
//                    }
//                ]
//            }
//        ]
//    },
//    {
//        "id": 2,
//        "name": "Aramis",
//        "post": [
//            {
//                "id": "4",
//                "author_id": "2",
//                "body": "Clara post #1",
//                "meta": [
//                    {
//                        "id": "4",
//                        "post_id": "4",
//                        "data": "meta 4"
//                    }
//                ],
//                "tag": [
//                    {
//                        "id": "7",
//                        "post_id": "4",
//                        "tag_id": "1",
//                        "tag_name": [
//                            {
//                                "id": "1",
//                                "name": "zim"
//                            }
//                        ]
//                    },
//                    {
//                        "id": "8",
//                        "post_id": "4",
//                        "tag_id": "2",
//                        "tag_name": [
//                            {
//                                "id": "2",
//                                "name": "dib"
//                            }
//                        ]
//                    },
//                    {
//                        "id": "9",
//                        "post_id": "4",
//                        "tag_id": "3",
//                        "tag_name": [
//                            {
//                                "id": "3",
//                                "name": "gir"
//                            }
//                        ]
//                    }
//                ]
//            },
//            {
//                "id": "5",
//                "author_id": "2",
//                "body": "Clara post #2",
//                "meta": [
//                    {
//                        "id": "5",
//                        "post_id": "5",
//                        "data": "meta 5"
//                    }
//                ],
//                "tag": [
//                    {
//                        "id": "10",
//                        "post_id": "5",
//                        "tag_id": "2",
//                        "tag_name": [
//                            {
//                                "id": "2",
//                                "name": "dib"
//                            }
//                        ]
//                    }
//                ]
//            }
//        ]
//    },
//    {
//        "id": 3,
//        "name": "Porthos",
//        "post": []
//    }
//]
