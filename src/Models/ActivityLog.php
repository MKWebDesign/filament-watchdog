<?php

namespace MKWebDesign\FilamentWatchdog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    protected $fillable = [
        'event_type',
        'user_id',
        'ip_address',
        'user_agent',
        'event_details',
        'risk_level',
        'metadata',
    ];

    protected $casts = [
        'event_details' => 'array',
        'metadata' => 'array',
    ];

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    public function scopeFailedLogins(Builder $query): Builder
    {
        return $query->where('event_type', 'failed_login');
    }

    public function scopeAdminActions(Builder $query): Builder
    {
        return $query->where('event_type', 'admin_action');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}