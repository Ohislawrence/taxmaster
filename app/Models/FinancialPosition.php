<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPosition extends Model
{
    protected $fillable = [
        'business_id',
        'position_date',
        'period_type',
        // Assets - Current
        'cash_and_bank',
        'trade_receivables',
        'inventory',
        'other_current_assets',
        // Assets - Non-Current
        'property_plant_equipment',
        'accumulated_depreciation',
        'intangible_assets',
        'other_non_current_assets',
        // Liabilities - Current
        'trade_payables',
        'tax_payable',
        'other_current_liabilities',
        // Liabilities - Non-Current
        'long_term_borrowings',
        'other_non_current_liabilities',
        // Equity
        'share_capital',
        'retained_earnings',
        'other_reserves',
        // Metadata
        'is_ai_generated',
        'ai_confidence',
        'notes',
    ];

    protected $casts = [
        'position_date' => 'date',
        'cash_and_bank' => 'decimal:2',
        'trade_receivables' => 'decimal:2',
        'inventory' => 'decimal:2',
        'other_current_assets' => 'decimal:2',
        'property_plant_equipment' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'intangible_assets' => 'decimal:2',
        'other_non_current_assets' => 'decimal:2',
        'trade_payables' => 'decimal:2',
        'tax_payable' => 'decimal:2',
        'other_current_liabilities' => 'decimal:2',
        'long_term_borrowings' => 'decimal:2',
        'other_non_current_liabilities' => 'decimal:2',
        'share_capital' => 'decimal:2',
        'retained_earnings' => 'decimal:2',
        'other_reserves' => 'decimal:2',
        'is_ai_generated' => 'boolean',
        'ai_confidence' => 'array',
    ];

    /**
     * Get the business that owns the financial position
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Calculate total assets
     */
    public function totalAssets(): float
    {
        return $this->currentAssets() + $this->nonCurrentAssets();
    }

    /**
     * Calculate current assets
     */
    public function currentAssets(): float
    {
        return (float) ($this->cash_and_bank
            + $this->trade_receivables
            + $this->inventory
            + $this->other_current_assets);
    }

    /**
     * Calculate non-current assets
     */
    public function nonCurrentAssets(): float
    {
        $ppe = (float) $this->property_plant_equipment - (float) $this->accumulated_depreciation;
        return $ppe
            + (float) $this->intangible_assets
            + (float) $this->other_non_current_assets;
    }

    /**
     * Calculate total liabilities
     */
    public function totalLiabilities(): float
    {
        return $this->currentLiabilities() + $this->nonCurrentLiabilities();
    }

    /**
     * Calculate current liabilities
     */
    public function currentLiabilities(): float
    {
        return (float) ($this->trade_payables
            + $this->tax_payable
            + $this->other_current_liabilities);
    }

    /**
     * Calculate non-current liabilities
     */
    public function nonCurrentLiabilities(): float
    {
        return (float) ($this->long_term_borrowings
            + $this->other_non_current_liabilities);
    }

    /**
     * Calculate total equity
     */
    public function totalEquity(): float
    {
        return (float) ($this->share_capital
            + $this->retained_earnings
            + $this->other_reserves);
    }

    /**
     * Check if balance sheet balances
     */
    public function isBalanced(float $tolerance = 0.01): bool
    {
        $assets = $this->totalAssets();
        $liabilitiesAndEquity = $this->totalLiabilities() + $this->totalEquity();

        return abs($assets - $liabilitiesAndEquity) <= $tolerance;
    }

    /**
     * Get the difference if not balanced
     */
    public function balanceDifference(): float
    {
        return $this->totalAssets() - ($this->totalLiabilities() + $this->totalEquity());
    }

    /**
     * Calculate current ratio (liquidity metric)
     */
    public function currentRatio(): ?float
    {
        $currentLiabilities = $this->currentLiabilities();

        if ($currentLiabilities == 0) {
            return null;
        }

        return $this->currentAssets() / $currentLiabilities;
    }

    /**
     * Calculate debt to equity ratio
     */
    public function debtToEquityRatio(): ?float
    {
        $equity = $this->totalEquity();

        if ($equity == 0) {
            return null;
        }

        return $this->totalLiabilities() / $equity;
    }
}
