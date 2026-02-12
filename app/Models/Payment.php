<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'app_id',
        'external_id',
        'midtrans_transaction_id',
        'amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'description',
        'status',
        'payment_method',
        'paid_at',
        'expired_at',
        'metadata',
        'midtrans_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
        'midtrans_response' => 'array',
    ];

    /**
     * Get the app that owns this payment
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Get all logs for this payment
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /**
     * Get webhook logs for this payment
     */
    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(array $data = []): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $data['payment_method'] ?? null,
            'midtrans_transaction_id' => $data['payment_id'] ?? null,
        ]);
    }

    /**
     * Mark payment as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Log an event for this payment
     */
    public function logEvent(string $event, array $payload): void
    {
        $this->logs()->create([
            'event' => $event,
            'payload' => $payload,
        ]);
    }
}
