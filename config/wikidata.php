<?php

/**
 * Various configurations relating to wikidata
 */
return [
    'api' => [
        'url' => env('WIKIDATA_API_URL', 'https://www.wikidata.org/w/api.php'),
        'response_cache' => [
            'ttl' => env('WIKIDATA_API_CACHE_TTL', 60 * 60 * 60 * 24) // 1 day arbitrarily chosen as default
        ]
    ]
];
