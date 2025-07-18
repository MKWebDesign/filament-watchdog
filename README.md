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

Then run the auto-installer script from your Laravel root directory:

```bash
bash install-filament-watchdog.sh
```

And migrate the database:

```bash
php artisan migrate
```

## Configuration

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