<?php



/**
 * Various configurations relating to imports
 */
return [
    'description' => [
        'max_length' => env('IMPORTS_DESCRIPTION_MAX', 350)
    ],

    'expires' => [
        'after' => env('IMPORTS_BEST_BEFORE_MIN', '+1 day')
    ]
];
