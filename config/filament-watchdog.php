<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => true,
        'scan_interval' => 60, // seconds
        'excluded_paths' => [
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'node_modules',
            'vendor',
            '.git',
            '.env',
        ],
        'monitored_paths' => [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Integrity Settings
    |--------------------------------------------------------------------------
    */
    'file_integrity' => [
        'enabled' => true,
        'hash_algorithm' => 'sha256',
        'store_file_contents' => false,
        'max_file_size' => 50 * 1024 * 1024, // 50MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Malware Detection Settings
    |--------------------------------------------------------------------------
    */
    'malware_detection' => [
        'enabled' => true,
        'scan_uploads' => true,
        'quarantine_enabled' => true,
        'quarantine_path' => storage_path('app/quarantine'),
        'signatures' => [
            'php_eval' => '/eval\s*\(/i',
            'php_system' => '/system\s*\(/i',
            'php_exec' => '/exec\s*\(/i',
            'php_shell_exec' => '/shell_exec\s*\(/i',
            'php_passthru' => '/passthru\s*\(/i',
            'base64_decode' => '/base64_decode\s*\(/i',
            'file_get_contents' => '/file_get_contents\s*\(\s*[\'"]https?:/i',
            'curl_exec' => '/curl_exec\s*\(/i',
            'web_shell' => '/\$_(?:GET|POST|REQUEST)\s*\[\s*[\'"](?:cmd|command|exec|system)/i',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Monitoring Settings
    |--------------------------------------------------------------------------
    */
    'activity_monitoring' => [
        'enabled' => true,
        'track_logins' => true,
        'track_admin_actions' => true,
        'track_file_changes' => true,
        'track_database_changes' => true,
        'failed_login_threshold' => 5,
        'ip_whitelist' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Settings
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => true,
        'email_enabled' => true,
        'email_recipients' => [
            'security@jouwdomein.com', // Vervang met jouw email
        ],
        'admin_emails' => [
            'admin@jouwdomein.com',     // Vervang met jouw admin email
            'security@jouwdomein.com',  // Vervang met jouw security email
        ],
        'alert_levels' => [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ],
        'rate_limiting' => [
            'enabled' => true,
            'max_alerts_per_hour' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Lockdown Settings
    |--------------------------------------------------------------------------
    */
    'emergency_lockdown' => [
        'default_options' => [
            'maintenance_mode' => true,
            'block_ips' => false,
            'disable_users' => false,
            'clear_sessions' => true,
            'htaccess_protection' => false,
            'notify_admins' => true,
            'emergency_backup' => true,
        ],
        'backup_retention_days' => 30,
        'auto_deactivate_hours' => 24, // Auto-deactivate after 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => 'mysql',
        'log_retention_days' => 30,
        'max_log_entries' => 100000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'memory_limit' => '512M',
        'execution_time_limit' => 300,
        'batch_size' => 100,
    ],
];