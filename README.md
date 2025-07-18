# FilamentWatchdog

Advanced security monitoring and intrusion detection plugin for FilamentPHP.

## Features

* Real-time file integrity monitoring
* Malware detection with signature scanning
* Activity logging and anomaly detection
* Email alerts for security events
* Forensic analysis tools
* Quarantine system for suspicious files

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

## Usage

Register the plugin in your `AdminPanelProvider`:

```php
use MKWebDesign\FilamentWatchdog\FilamentWatchdogPlugin;

// Inside the panel() method:
->plugin(FilamentWatchdogPlugin::make());
```

After installation, visit:

```
/admin/security/dashboard
```

to access the security monitoring interface.

## Author

Martin Knops | MKWebDesign

* Website: [https://mkwebdesign.nl](https://mkwebdesign.nl)
* Email: [info@mkwebdesign.nl](mailto:info@mkwebdesign.nl)

## License

MIT License
