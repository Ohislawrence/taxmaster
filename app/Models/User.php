<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'affiliate_code',
        'affiliate_commission_percent',
        'affiliate_bank_name',
        'affiliate_bank_account_name',
        'affiliate_bank_account_number',
        'affiliate_bank_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'affiliate_commission_percent' => 'decimal:2',
            'affiliate_bank_name' => 'string',
            'affiliate_bank_account_name' => 'string',
            'affiliate_bank_account_number' => 'string',
            'affiliate_bank_code' => 'string',
        ];
    }

    protected static function booted()
    {
        static::saved(function (self $user) {
            // If user is an accountant and lacks an affiliate code, generate one.
            try {
                if (method_exists($user, 'hasRole') && $user->hasRole('accountant')) {
                    $changed = false;

                    if (! $user->affiliate_code) {
                        do {
                            $code = strtoupper(Str::random(8));
                        } while (self::where('affiliate_code', $code)->exists());

                        $user->forceFill(['affiliate_code' => $code]);
                        $changed = true;
                    }

                    if (is_null($user->affiliate_commission_percent)) {
                        $user->forceFill(['affiliate_commission_percent' => 10.00]);
                        $changed = true;
                    }

                    if ($changed) {
                        $user->saveQuietly();
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to ensure affiliate data for user: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get the business owned by this user
     */
    public function ownedBusiness()
    {
        return $this->hasOne(Business::class, 'owner_id');
    }

    /**
     * Get all businesses owned/managed by this user
     */
    public function businesses()
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    /**
     * Businesses explicitly assigned to this accountant (pivot)
     */
    public function managedBusinesses()
    {
        return $this->belongsToMany(Business::class, 'accountant_business', 'user_id', 'business_id')
            ->withTimestamps();
    }

    /**
     * Determine whether the user manages (owns) the given business id or model
     */
    public function managesBusiness($business): bool
    {
        $id = $business instanceof \App\Models\Business ? $business->id : $business;

        // Owner match
        if ($this->ownedBusiness?->id === $id) {
            return true;
        }

        // Businesses the user created (owner_id)
        if ($this->businesses()->where('id', $id)->exists()) {
            return true;
        }

        // Businesses explicitly assigned to the accountant by admin (pivot)
        return $this->managedBusinesses()->where('business_id', $id)->exists();
    }

    /**
     * Return the default business context for this user.
     * For single-business users this is `ownedBusiness`, for accountants it's the first managed business.
     */
    public function defaultBusiness()
    {
        // Prefer owned business, then owned list, then managed businesses
        return $this->ownedBusiness ?? $this->businesses()->first() ?? $this->managedBusinesses()->first();
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a business owner/manager
     */
    public function isBusiness(): bool
    {
        return $this->hasRole('business');
    }

    /**
     * Send the email verification notification using our custom notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }
}
