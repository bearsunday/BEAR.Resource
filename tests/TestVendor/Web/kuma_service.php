<?php

return [
    'baseUrl' => 'http://httpbin.org/',
    'operations' => [
        'GET:bear_resource' => [
            'httpMethod' => 'GET',
            'uri' => '/get',
            'responseModel' => 'getResponse',
            'parameters' => [
                'foo' => [
                    'type' => 'string',
                    'location' => 'query'
                ]
            ],
            '_link' => [
                'friend' => 'app://self/friend?{id}'
            ]
        ],
        'POST:bear_resource' => [
            'httpMethod' => 'POST',
            'uri' => '/post',
            'responseModel' => 'getResponse',
            'parameters' => [
                'id' => [
                    'type' => 'string',
                    'location' => 'query'
                ],
                'name' => [
                    'type' => 'string',
                    'location' => 'postField'
                ]
            ],
            '_link' => [
                'friend' => 'app://self/new_post/?{title}'
            ]
        ],
        'GET:bear_404_resource' => [
            'httpMethod' => 'GET',
            'uri' => '/get_invalid',
        ]
    ],
    'models' => [
        'getResponse' => [
            'type' => 'object',
            'additionalProperties' => [
                'location' => 'json'
            ]
        ]
    ]
];
