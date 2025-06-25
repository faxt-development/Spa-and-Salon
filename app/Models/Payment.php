<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'order_id',
        'client_id',
        'staff_id',
        'payment_method',
        'amount',
        'transaction_id',
        'status',
        'payment_date',
        'notes',
        'tip_amount',
        'tax_amount',
        'discount_amount',
        'is_refunded',
        'refunded_amount',
        'refunded_at',
        'payment_gateway',
        'card_last_four',
        'card_brand',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'refunded_at' => 'datetime',
        'is_refunded' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'payment_date',
        'refunded_at',
        'deleted_at',
    ];

    /**
     * Payment status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * Get the appointment that owns the payment.
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the order that owns the payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the client that made the payment.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the staff member who processed the payment.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the payment gateway details.
     */
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway');
    }

    /**
     * Get the parent payment model (if this is a refund).
     */
    public function parentPayment()
    {
        return $this->belongsTo(Payment::class, 'parent_payment_id');
    }

    /**
     * Get the refunds for this payment.
     */
    public function refunds()
    {
        return $this->hasMany(Payment::class, 'parent_payment_id');
    }

    /**
     * Check if the payment is refundable.
     *
     * @return bool
     */
    public function isRefundable(): bool
    {
        return $this->status === self::STATUS_COMPLETED &&
               !$this->is_refunded &&
               $this->amount > 0;
    }

    /**
     * Get the total amount that can still be refunded.
     *
     * @return float
     */
    public function getRefundableAmount(): float
    {
        if (!$this->isRefundable()) {
            return 0;
        }

        $refundedAmount = $this->refunds()->sum('refunded_amount');
        return max(0, $this->amount - $refundedAmount);
    }
}
