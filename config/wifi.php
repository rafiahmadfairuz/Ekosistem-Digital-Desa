<?php

return [
    /*
     * Keep the simulated gateway as the default. The MikroTik adapter is a
     * deliberately fail-closed integration boundary until a captive portal
     * and router/RADIUS deployment has been configured and verified.
     */
    'gateway' => env('WIFI_GATEWAY', 'simulated'),

    'access_log_retention_days' => (int) env('WIFI_ACCESS_LOG_RETENTION_DAYS', 90),
];
