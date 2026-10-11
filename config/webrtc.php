<?php

return [
    'turn' => [
        // OpenRelay is a public test/fallback service only; production should use private credentials.
        'urls' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('WEBRTC_TURN_URLS', ''))
        ))),
        'username' => env('WEBRTC_TURN_USERNAME'),
        'credential' => env('WEBRTC_TURN_CREDENTIAL'),
    ],
];
