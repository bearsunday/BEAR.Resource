<?php

namespace BEAR\Resource;

/**
 * Behavior test
 */
class DemoTest extends \PHPUnit_Framework_TestCase
{
    public function testMin()
    {
        $expected = <<<EOT
code:200
headers:
Array
(
)
body:
Array
(
    [name] => Aramis
    [age] => 16
    [blog_id] => 1
)

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/00.min/run.php';
    }

    public function testBasic()
    {
        $expected = <<<EOT
code:200
headers:
Array
(
)
body:
Array
(
    [name] => Aramis
    [age] => 16
    [blog_id] => 1
)

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/01.basic/run.php';
    }

    public function testLinkSelf()
    {
        $expected = <<<EOT
{
    "name": "Porthos",
    "age": 17,
    "blog_id": 2,
    "blog": {
        "name": "Porthos blog"
    },
    "_links": {
        "self": {
            "href": "/user?id=2"
        },
        "blog": {
            "href": "app://self/blog?id=2"
        }
    }
}

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/02.link-self/run.php';
    }

    public function testLinkCrawl()
    {
        $expected = <<<EOT
[
    {
        "id": 1,
        "name": "Athos",
        "post": [
            {
                "id": "1",
                "author_id": "1",
                "body": "Anna post #1",
                "meta": [
                    {
                        "id": "1",
                        "post_id": "1",
                        "data": "meta 1"
                    }
                ],
                "tag": [
                    {
                        "id": "1",
                        "post_id": "1",
                        "tag_id": "1",
                        "tag_name": [
                            {
                                "id": "1",
                                "name": "zim"
                            }
                        ]
                    },
                    {
                        "id": "2",
                        "post_id": "1",
                        "tag_id": "2",
                        "tag_name": [
                            {
                                "id": "2",
                                "name": "dib"
                            }
                        ]
                    }
                ]
            },
            {
                "id": "2",
                "author_id": "1",
                "body": "Anna post #2",
                "meta": [
                    {
                        "id": "2",
                        "post_id": "2",
                        "data": "meta 2"
                    }
                ],
                "tag": [
                    {
                        "id": "3",
                        "post_id": "2",
                        "tag_id": "2",
                        "tag_name": [
                            {
                                "id": "2",
                                "name": "dib"
                            }
                        ]
                    },
                    {
                        "id": "4",
                        "post_id": "2",
                        "tag_id": "3",
                        "tag_name": [
                            {
                                "id": "3",
                                "name": "gir"
                            }
                        ]
                    }
                ]
            },
            {
                "id": "3",
                "author_id": "1",
                "body": "Anna post #3",
                "meta": [
                    {
                        "id": "3",
                        "post_id": "3",
                        "data": "meta 3"
                    }
                ],
                "tag": [
                    {
                        "id": "5",
                        "post_id": "3",
                        "tag_id": "3",
                        "tag_name": [
                            {
                                "id": "3",
                                "name": "gir"
                            }
                        ]
                    },
                    {
                        "id": "6",
                        "post_id": "3",
                        "tag_id": "1",
                        "tag_name": [
                            {
                                "id": "1",
                                "name": "zim"
                            }
                        ]
                    }
                ]
            }
        ]
    },
    {
        "id": 2,
        "name": "Aramis",
        "post": [
            {
                "id": "4",
                "author_id": "2",
                "body": "Clara post #1",
                "meta": [
                    {
                        "id": "4",
                        "post_id": "4",
                        "data": "meta 4"
                    }
                ],
                "tag": [
                    {
                        "id": "7",
                        "post_id": "4",
                        "tag_id": "1",
                        "tag_name": [
                            {
                                "id": "1",
                                "name": "zim"
                            }
                        ]
                    },
                    {
                        "id": "8",
                        "post_id": "4",
                        "tag_id": "2",
                        "tag_name": [
                            {
                                "id": "2",
                                "name": "dib"
                            }
                        ]
                    },
                    {
                        "id": "9",
                        "post_id": "4",
                        "tag_id": "3",
                        "tag_name": [
                            {
                                "id": "3",
                                "name": "gir"
                            }
                        ]
                    }
                ]
            },
            {
                "id": "5",
                "author_id": "2",
                "body": "Clara post #2",
                "meta": [
                    {
                        "id": "5",
                        "post_id": "5",
                        "data": "meta 5"
                    }
                ],
                "tag": {
                    "0": {
                        "id": "10",
                        "post_id": "5",
                        "tag_id": "2"
                    },
                    "tag_name": []
                }
            }
        ]
    },
    {
        "id": 3,
        "name": "Porthos",
        "post": []
    }
]

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/03.link-crawl/run.php';
    }

    public function testRestBucks()
    {
        $expected = <<<EOT
201: Created
Location: app://self/Order/?id=12345
Order: Success

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/04.restbucks/run.php';
    }
    public function testHal()
    {
        $expected = <<<EOT
{
    "headline": "40th anniversary of Rubik's Cube invention.",
    "sports": "Pieter Weening wins Giro d'Italia.",
    "_embedded": {
        "weather": {
            "today": "the weather of today is sunny",
            "_links": {
                "self": {
                    "href": "/weather?date=today"
                },
                "tomorrow": {
                    "href": "/weather/tomorrow"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/news?date=today"
        }
    }
}

EOT;
        $this->expectOutputString($expected);
        require dirname(__DIR__) . '/docs/demo/06.HAL/run.php';
    }
}
