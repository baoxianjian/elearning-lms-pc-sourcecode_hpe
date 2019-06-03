<?php
return [
    'createPost' => [
        'type' => 2,
        'description' => 'Create a post',
    ],
    'updatePost' => [
        'type' => 2,
        'description' => 'Update post',
    ],
    'author' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'createPost',
            'updateOwnPost',
        ],
    ],
    'updateOwnPost' => [
        'type' => 2,
        'description' => 'Update own post',
        'ruleName' => 'isAuthor',
        'children' => [
            'updatePost',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'updatePost',
            'author',
        ],
    ],
];
