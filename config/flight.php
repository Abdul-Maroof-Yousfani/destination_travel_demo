<?php
    return [

        /*
        |----------------------------------------------------------------------
        | Carriers that should be skipped when BOTH origin and destination
        | are non-local airports (isLocal = false)
        |----------------------------------------------------------------------
        */
        'skip_local' => [
            'emirates',      // lower-case carrier name returned by getCarrierName()
            // 'another_carrier',
            // 'yet_another',
        ],

    ];
