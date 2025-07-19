<?php

namespace MKWebDesign\FilamentWatchdog\Traits;

trait ConfiguresWatchdogNavigation
{
    /**
     * Override this method in each page to define visibility behavior
     */
    protected static function getNavigationVisibility(): string
    {
        return 'conditional'; // 'always', 'conditional', 'never'
    }

    /**
     * Determine if this resource/page should show in navigation
     */
    public static function shouldRegisterNavigation(): bool
    {
        // Check if security navigation is completely hidden
        if (config('filament-watchdog.navigation.security.hidden', false)) {
            return false;
        }

        // Check if only_on_security_pages is enabled
        if (config('filament-watchdog.navigation.security.only_on_security_pages', false)) {
            // If this setting is true, only show items when on security dashboard
            return static::isOnSecurityDashboard();
        }

        // Normal behavior based on visibility setting
        $visibility = static::getNavigationVisibility();

        switch ($visibility) {
            case 'always':
                // Security Dashboard - always visible everywhere
                return true;
            case 'conditional':
                // Other security items - only visible on ANY security page
                return static::isOnAnySecurityPage();
            case 'never':
            default:
                return false;
        }
    }

    /**
     * Static method to check if ANY security page should register navigation
     * Call this from pages that don't use the trait
     */
    public static function shouldAnySecurityPageRegister(): bool
    {
        // Check if security navigation is completely hidden
        if (config('filament-watchdog.navigation.security.hidden', false)) {
            return false;
        }

        // Check if only_on_security_pages is enabled
        if (config('filament-watchdog.navigation.security.only_on_security_pages', false)) {
            $currentRoute = request()->route()?->getName() ?? '';
            return $currentRoute === 'filament.admin.pages.security.dashboard';
        }

        // For non-dashboard pages, only show when on security dashboard
        $currentRoute = request()->route()?->getName() ?? '';
        return $currentRoute === 'filament.admin.pages.security.dashboard';
    }

    /**
     * Check if we're currently on the security dashboard
     */
    protected static function isOnSecurityDashboard(): bool
    {
        $currentRoute = request()->route()?->getName() ?? '';
        return $currentRoute === 'filament.admin.pages.security.dashboard';
    }

    /**
     * Check if we're currently on ANY security page
     */
    protected static function isOnAnySecurityPage(): bool
    {
        $currentRoute = request()->route()?->getName() ?? '';

        $securityRoutes = [
            'security.dashboard',
            'security',
            'file-integrity',
            'threat-timeline',
            'malware-detection',
            'forensic-analysis',
            'activity-logs',
            'security-alerts'
        ];

        foreach ($securityRoutes as $route) {
            if (str_contains($currentRoute, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the navigation group for security items
     */
    public static function getNavigationGroup(): ?string
    {
        if (config('filament-watchdog.navigation.security.hidden', false)) {
            return null;
        }

        return 'Security';
    }

    /**
     * Get the navigation sort order
     */
    public static function getNavigationSort(): ?int
    {
        return config('filament-watchdog.navigation.security.sort', 100) + static::getDefaultSecuritySort();
    }

    /**
     * Get default security sort - override this in individual pages
     */
    protected static function getDefaultSecuritySort(): int
    {
        return 0;
    }

    /**
     * Get security navigation icon
     */
    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon ?? 'heroicon-o-shield-check';
    }
}