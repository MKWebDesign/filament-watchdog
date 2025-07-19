# FilamentWatchdog

Advanced security monitoring and intrusion detection plugin for FilamentPHP.

## Features

* Real-time file integrity monitoring
* Malware detection with signature scanning
* Activity logging and anomaly detection
* Email alerts for security events
* Forensic analysis tools
* Quarantine system for suspicious files
* Emergency lockdown system

## Installation

Install the package via Composer:

```bash
composer require mkwebdesign/filament-watchdog:^1.0
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=filament-watchdog-config
```

Optionally, publish the views (if you want to customize them):

```bash
php artisan vendor:publish --tag=filament-watchdog-views
```

Then run the auto-installer script from your Laravel root directory:

```bash
bash install-filament-watchdog.sh
```

And migrate the database:

```bash
php artisan migrate
```

## Configuration

After publishing the config file, you can customize the settings in `config/filament-watchdog.php`:

```php
return [
    'monitoring' => [
        'enabled' => true,
        'scan_interval' => 'everyMinute', // How often to run scans
    ],
    'alerts' => [
        'email' => [
            'enabled' => true,
            'recipients' => ['admin@yoursite.com'],
        ],
    ],
    'emergency' => [
        'auto_publish_views' => true,
        'lockdown_enabled' => true,
    ],
    // ... more settings
];
```

### Cronjob Setup (Required)

FilamentWatchdog requires the Laravel scheduler to run automated security scans. Add the standard Laravel cronjob to your server:

```bash
# Edit your crontab
crontab -e

# Add this line (standard Laravel scheduler - runs every minute)
* * * * * cd /path/to/your/laravel/project && php artisan schedule:run >> /dev/null 2>&1
```

**Example with full path:**
```bash
* * * * * php /home/username/domains/yoursite.com/public_html/artisan schedule:run >> /dev/null 2>&1
```

**Note:** This is the standard Laravel scheduler cronjob that should run every minute. FilamentWatchdog will automatically schedule its security scans within this framework.

**Manual scan commands:**

```bash
# Run file integrity scan
php artisan watchdog:scan-integrity

# Run malware detection scan
php artisan watchdog:scan-malware

# Create new file baseline
php artisan watchdog:create-baseline

# Run complete security scan
php artisan watchdog:scan-all
```

### Panel Registration

Register the plugin in your `AdminPanelProvider`:

```php
use MKWebDesign\FilamentWatchdog\FilamentWatchdogPlugin;

// Inside the panel() method:
->plugin(FilamentWatchdogPlugin::make());
```

## Usage

After installation, visit:

```
/admin/security/dashboard
```

to access the security monitoring interface.

### Key Features:

* **Security Dashboard** - Overview of system security status
* **File Monitoring** - Real-time detection of file changes
* **Malware Scanner** - Automated scanning for malicious files
* **Alert System** - Email notifications for security events
* **Emergency Lockdown** - Immediate site protection in case of threats
* **Quarantine System** - Safe isolation of suspicious files

## Requirements

* PHP 8.1 or higher
* Laravel 10.x or 11.x
* FilamentPHP 3.x
* Cronjob access on your server

## Troubleshooting

### Config Not Published?

If the configuration file doesn't appear in `config/filament-watchdog.php`, try:

```bash
# Clear caches first
php artisan config:clear
php artisan cache:clear

# Then publish again
php artisan vendor:publish --tag=filament-watchdog-config --force

# Verify the file exists
ls -la config/filament-watchdog.php
```

### Cronjob Not Working?

1. Check if the Laravel scheduler is configured correctly
2. Verify the path to your Laravel project in the cronjob
3. Ensure PHP is in your server's PATH
4. Check cronjob logs: `grep CRON /var/log/syslog`

### Permission Issues?

Make sure the web server user has write permissions to:
- `storage/app/` directory
- `storage/logs/` directory
- `storage/app/quarantine/` directory

## Author

Martin Knops | MKWebDesign

* Website: [https://mkwebdesign.nl](https://mkwebdesign.nl)
* Email: [info@mkwebdesign.nl](mailto:info@mkwebdesign.nl)

## License

MIT License