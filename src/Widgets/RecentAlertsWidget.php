<?php

namespace MKWebDesign\FilamentWatchdog\Widgets;

use Filament\Widgets\Widget;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;

class RecentAlertsWidget extends Widget
{
    protected static string $view = 'filament-watchdog::widgets.recent-alerts';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        return [
            'alerts' => SecurityAlert::latest()
                ->take(10)
                ->get()
                ->map(function ($alert) {
                    return [
                        'id' => $alert->id,
                        'title' => $alert->title,
                        'severity' => $alert->severity,
                        'status' => $alert->status,
                        'created_at' => $alert->created_at,
                        'time_ago' => $alert->created_at->diffForHumans(),
                        'description' => $alert->description,
                    ];
                })
        ];
    }
}
