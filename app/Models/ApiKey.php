<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'key',
        'key_hash',
        'is_active',
        'permissions',
        'expires_at',
    ];

    protected $hidden = [
        'key',
        'key_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a new API key
     */
    public static function generate(string $name, string $description = null, array $permissions = [], Carbon $expiresAt = null): self
    {
        $key = 'ck_' . Str::random(60); // "ck_" prefix for cook-off keys

        return self::create([
            'name' => $name,
            'key' => $key,
            'key_hash' => hash('sha256', $key),
            'description' => $description,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Check if the API key is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if the key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // If no permissions set, allow all
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get available permissions
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'export.contests' => 'Export Contests',
            'export.votes' => 'Export Votes',
            'export.entries' => 'Export Entries',
            'export.vote-ratings' => 'Export Vote Ratings',
            'export.single-contest' => 'Export Single Contest',
        ];
    }

    /**
     * Verify an API key against hash
     */
    public static function verify(string $key): ?self
    {
        $hash = hash('sha256', $key);

        return self::where('key_hash', $hash)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Accessor for masked key display
     */
    public function getMaskedKeyAttribute(): string
    {
        if (!$this->key) {
            return 'Key not available';
        }

        return substr($this->key, 0, 8) . str_repeat('*', 52) . substr($this->key, -3);
    }

    /**
     * Accessor for status badge
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }
}
