<?php

/**
 * Various configurations relating to mismatches
 */
return [
    'statuses' => [
        'default' => 'pending',
        'available' => [
            'pending',
            'wikidata',
            'external',
            'both',
            'none'
        ]
    ],

    'validation' => [
        'guid' => [
            'max_length' => 100,
            'format' => '/^Q\d+\$[0-9A-F]{8}\-[0-9A-F]{4}\-4[0-9A-F]{3}\-[89AB][0-9A-F]{3}\-[0-9A-F]{12}$/i'
        ]
    ]
];
