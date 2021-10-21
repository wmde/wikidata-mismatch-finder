<?php



/**
 * Various configurations relating to imports
 */
return [
    'upload' => [
        'filename_template' => ':datetime-mismatch-upload.:userid.csv',
        'column_keys' => [
            'statement_guid',
            'property_id',
            'wikidata_value',
            'external_value',
            'external_url'
        ]
    ],

    'description' => [
        'max_length' => env('IMPORTS_DESCRIPTION_MAX', 350)
    ],

    'external_source' => [
        'max_length' => env('IMPORTS_EXTERNAL_SOURCE_MAX', 100)
    ],

    'external_source_url' => [
        'max_length' => env('IMPORTS_EXTERNAL_SOURCE_URL_MAX', 1500)
    ],

    'expires' => [
        'after' => env('IMPORTS_BEST_BEFORE_MIN', '+1 day')
    ]
];
