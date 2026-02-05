<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class App extends Model
{
    protected $fillable = [
        'name',
        'description',
        'access_token',
        'webhook_url',
        'success_redirect_url',
        'failure_redirect_url',
        'is_active',
        'created_by',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Generate a unique access token
     */
    public static function generateAccessToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('access_token', $token)->exists());

        return $token;
    }

    /**
     * Get the user who created this app
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all payments for this app
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get webhook logs for this app
     */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Regenerate the access token
     */
    public function regenerateToken(): string
    {
        $newToken = self::generateAccessToken();
        $this->access_token = $newToken;
        $this->save();

        return $newToken;
    }
}
