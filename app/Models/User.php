<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'display_name',
        'email',
        'password',
        'phone',
        'avatar',
        'address',
        'role',
        'is_active',
        'is_guest',
        'last_active_at',
        'notification_preference',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_guest' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function consignedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function consignorBalance(): HasOne
    {
        return $this->hasOne(ConsignorBalance::class);
    }

    public function consignorPayouts(): HasMany
    {
        return $this->hasMany(ConsignorPayout::class);
    }

    public function hasConsignedProducts(): bool
    {
        return $this->consignedProducts()->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer' || $this->is_guest;
    }

    public function isGuest(): bool
    {
        return (bool) $this->is_guest;
    }

    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->attributes['display_name'])) {
            return $this->attributes['display_name'];
        }

        if (!empty($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return 'Guest #' . strtoupper(Str::random(4));
    }

    /**
     * Generate a stable, human-friendly guest label (e.g. "Guest #A7K2").
     */
    public static function generateGuestLabel(): string
    {
        return 'Guest #' . strtoupper(Str::random(4));
    }

    /**
     * Get formatted phone number for WhatsApp
     *
     * @return string
     */
    public function getWhatsAppPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone ?? '');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Check if user prefers WhatsApp notifications
     *
     * @return bool
     */
    public function prefersWhatsApp(): bool
    {
        return $this->notification_preference === 'whatsapp';
    }

    /**
     * Check if user prefers email notifications
     *
     * @return bool
     */
    public function prefersEmail(): bool
    {
        return $this->notification_preference === 'email' && !empty($this->email);
    }
}
