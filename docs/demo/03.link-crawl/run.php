<?php

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $author = $resource
              ->get
              ->uri('app://self/author')
              ->linkCrawl('tree')
              ->eager
              ->request();
}

output: {
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
//                "tag": {
//                "0": {
//                    "id": "10",
//                        "post_id": "5",
//                        "tag_id": "2"
//                    },
//                    "tag_name": []
//                }
//            }
//        ]
//    },
//    {
//        "id": 3,
//        "name": "Porthos",
//        "post": []
//    }
//]
