<?php

/**
 * Various configurations relating to mismatches
 */
return [
    'validation' => [
        'guid' => [
            'max_length' => 100,
            // Q<INTEGER>$<UUID>: The uuid format is general and not restricted to any spec version
            'format' => '/^Q\d+\$[0-9A-F]{8}\-[0-9A-F]{4}\-[0-9A-F]{4}\-[0-9A-F]{4}\-[0-9A-F]{12}$/i'
        ],
        'pid' => [
            'max_length' => 100,
            'format' => '/^P\d+$/i'
        ],
        'wikidata_value' => [
            'max_length' => 1500 // Longest allowed value on wikidata
        ],
        'meta_wikidata_value' => [
            'max_length' => 100,
            'format' => '/\AQ[1-9]\d*\z/'
        ],
        'external_value' => [
            'max_length' => 1500 // Longest allowed value on wikidata
        ],
        'external_url' => [
            'max_length' => 1500 // Longest allowed value on wikidata
        ],
        'item_id' => [
            'max_length' => 12,
            'format' => '/\AQ[1-9]\d*\z/'
        ],
        'ids' => [
            'max' => 600 // Max number of item-ids to fetch mismatches for at once
        ],
        'review_status' => [
            'accepted_values' => ['pending', 'wikidata', 'missing', 'external', 'both', 'none']
        ],
        'type' => [
            'accepted_values' => ['statement', 'qualifier']
        ]
    ],
    'id_separator' => '|',
];
