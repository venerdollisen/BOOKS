<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'account_type',
        'parent_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent account if this is a sub-account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get all child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get all transaction items for this account.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get account type for reporting (normalize account_type field)
     */
    public function getTypeAttribute()
    {
        $typeMap = [
            'asset' => 'asset',
            'Asset' => 'asset',
            'liability' => 'liability',
            'Liability' => 'liability',
            'equity' => 'equity',
            'Equity' => 'equity',
            'revenue' => 'revenue',
            'Revenue' => 'revenue',
            'income' => 'revenue',
            'Income' => 'revenue',
            'expense' => 'expense',
            'Expense' => 'expense',
        ];

        return $typeMap[$this->account_type] ?? strtolower($this->account_type);
    }
}
