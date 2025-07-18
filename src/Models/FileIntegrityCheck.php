<?php

namespace MKWebDesign\FilamentWatchdog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FileIntegrityCheck extends Model
{
    protected $fillable = [
        'file_path',
        'file_hash',
        'file_size',
        'last_modified',
        'status',
        'changes',
    ];

    protected $casts = [
        'last_modified' => 'datetime',
        'changes' => 'array',
    ];

    public function scopeModified(Builder $query): Builder
    {
        return $query->where('status', 'modified');
    }

    public function scopeDeleted(Builder $query): Builder
    {
        return $query->where('status', 'deleted');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}