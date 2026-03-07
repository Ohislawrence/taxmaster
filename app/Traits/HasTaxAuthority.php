<?php

namespace App\Traits;

/**
 * HasTaxAuthority Trait
 *
 * Provides tax authority distinction for Nigerian tax returns.
 * - FIRS: Federal Inland Revenue Service (VAT, CIT, WHT on companies, Stamp Duty)
 * - SIRS: State Internal Revenue Service (PAYE, WHT on individuals)
 */
trait HasTaxAuthority
{
    public const TAX_AUTHORITY_FIRS = 'firs';
    public const TAX_AUTHORITY_SIRS = 'sirs';

    /**
     * Get the default tax authority for this return type.
     * Override in models that default to SIRS (e.g., PayeReturn).
     */
    public function getDefaultTaxAuthority(): string
    {
        return self::TAX_AUTHORITY_FIRS;
    }

    /**
     * Boot the trait — set default tax authority on creating.
     */
    public static function bootHasTaxAuthority(): void
    {
        static::creating(function ($model) {
            if (empty($model->tax_authority)) {
                $model->tax_authority = $model->getDefaultTaxAuthority();
            }
        });
    }

    /**
     * Get the tax authority label.
     */
    public function getTaxAuthorityLabelAttribute(): string
    {
        return match ($this->tax_authority) {
            'firs' => 'FIRS (Federal)',
            'sirs' => 'SIRS (State)',
            default => strtoupper($this->tax_authority ?? 'Unknown'),
        };
    }

    /**
     * Check if this return is under federal jurisdiction.
     */
    public function isFederal(): bool
    {
        return $this->tax_authority === self::TAX_AUTHORITY_FIRS;
    }

    /**
     * Check if this return is under state jurisdiction.
     */
    public function isState(): bool
    {
        return $this->tax_authority === self::TAX_AUTHORITY_SIRS;
    }

    /**
     * Scope: filter by tax authority.
     */
    public function scopeTaxAuthority($query, string $authority)
    {
        return $query->where('tax_authority', $authority);
    }

    /**
     * Scope: federal returns only.
     */
    public function scopeFederal($query)
    {
        return $query->where('tax_authority', self::TAX_AUTHORITY_FIRS);
    }

    /**
     * Scope: state returns only.
     */
    public function scopeState($query)
    {
        return $query->where('tax_authority', self::TAX_AUTHORITY_SIRS);
    }
}
