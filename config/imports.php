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

    'expires' => [
        'after' => env('IMPORTS_BEST_BEFORE_MIN', '+1 day')
    ]
];
