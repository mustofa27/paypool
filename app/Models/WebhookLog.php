<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'app_id',
        'payment_id',
        'event_type',
        'payload',
        'response',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the app that owns this webhook log
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Get the payment related to this webhook
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->created_at = now();
        });
    }
}
