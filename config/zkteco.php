<?php

// =============================================================================
// ZKTeco Configuration - Device communication settings
// =============================================================================

return [
    /*
    |--------------------------------------------------------------------------
    | Default Port
    |--------------------------------------------------------------------------
    |
    | The default port for ZKTeco device communication. Most ZKTeco fingerprint
    | and face recognition devices use port 4370 for UDP communication.
    |
    */
    'default_port' => env('ZKTECO_DEFAULT_PORT', 4370),

    /*
    |--------------------------------------------------------------------------
    | Connection Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait when establishing a connection to a device.
    |
    */
    'connection_timeout' => env('ZKTECO_CONNECTION_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Read Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for a response from the device.
    |
    */
    'read_timeout' => env('ZKTECO_READ_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | Number of retry attempts for failed device connections.
    |
    */
    'max_retries' => env('ZKTECO_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | Delay in milliseconds between retry attempts.
    |
    */
    'retry_delay' => env('ZKTECO_RETRY_DELAY', 1000),

    /*
    |--------------------------------------------------------------------------
    | Polling Interval
    |--------------------------------------------------------------------------
    |
    | Default interval in minutes for automatic device status polling.
    |
    */
    'polling_interval' => env('ZKTECO_POLLING_INTERVAL', 5),

    /*
    |--------------------------------------------------------------------------
    | Sync Interval
    |--------------------------------------------------------------------------
    |
    | Default interval in minutes for syncing attendance logs from devices.
    |
    */
    'sync_interval' => env('ZKTECO_SYNC_INTERVAL', 5),

    /*
    |--------------------------------------------------------------------------
    | Logging Channel
    |--------------------------------------------------------------------------
    |
    | Log channel for device communication logs. Set to null to disable logging.
    |
    */
    'log_channel' => env('ZKTECO_LOG_CHANNEL', 'zkteco'),
];
