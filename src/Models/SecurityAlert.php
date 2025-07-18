<?php

namespace MKWebDesign\FilamentWatchdog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SecurityAlert extends Model
{
    protected $fillable = [
        'alert_type',
        'title',
        'description',
        'severity',
        'status',
        'metadata',
        'acknowledged_at',
        'resolved_at',
        'acknowledged_by',
        'resolved_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'acknowledged']);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
        'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary',
        };
    }
}