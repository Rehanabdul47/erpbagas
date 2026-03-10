<?php

namespace App\Traits;

use App\Models\Branch;

trait BranchScope
{
    /**
     * Check if current user has Executive role (sees all data).
     */
    protected function isExecutive(): bool
    {
        return auth()->user()->hasRole('Executive');
    }

    /**
     * Get current user's branch_id.
     */
    protected function getUserBranchId(): ?int
    {
        return auth()->user()->branch_id;
    }

    /**
     * Apply branch scoping to a query that has a direct 'branch_id' column.
     * Executive users bypass the filter. If $branchIdFilter is provided (e.g. from request),
     * Executive users can filter by specific branch.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $branchIdFilter Optional branch filter from request ('all' or branch id)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeByBranch($query, ?string $branchIdFilter = null)
    {
        if ($this->isExecutive()) {
            // Executive can optionally filter by branch
            if ($branchIdFilter && $branchIdFilter !== 'all') {
                $query->where('branch_id', $branchIdFilter);
            }
            return $query;
        }

        $branchId = $this->getUserBranchId();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    /**
     * Apply branch scoping via a relationship (e.g. filter attendance by employee's branch).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relation The relationship name (e.g. 'employee')
     * @param string|null $branchIdFilter Optional branch filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeByRelationBranch($query, string $relation, ?string $branchIdFilter = null)
    {
        if ($this->isExecutive()) {
            if ($branchIdFilter && $branchIdFilter !== 'all') {
                $query->whereHas($relation, function ($q) use ($branchIdFilter) {
                    $q->where('branch_id', $branchIdFilter);
                });
            }
            return $query;
        }

        $branchId = $this->getUserBranchId();

        if ($branchId) {
            $query->whereHas($relation, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return $query;
    }

    /**
     * Apply branch scoping for user_id-based tables (no branch_id column).
     * Filters by users belonging to the same branch.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $branchIdFilter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeByUserBranch($query, ?string $branchIdFilter = null)
    {
        if ($this->isExecutive()) {
            if ($branchIdFilter && $branchIdFilter !== 'all') {
                $userIds = \App\Models\User::where('branch_id', $branchIdFilter)->pluck('id');
                $query->whereIn('user_id', $userIds);
            }
            return $query;
        }

        $branchId = $this->getUserBranchId();

        if ($branchId) {
            $userIds = \App\Models\User::where('branch_id', $branchId)->pluck('id');
            $query->whereIn('user_id', $userIds);
        }

        return $query;
    }

    /**
     * Get branches list for dropdown filters.
     * Executive sees all active branches, others see only their branch.
     */
    protected function getBranchesForFilter()
    {
        if ($this->isExecutive()) {
            return Branch::where('is_active', true)->get();
        }

        $branchId = $this->getUserBranchId();
        return $branchId ? Branch::where('id', $branchId)->get() : collect();
    }

    /**
     * Scope product query by branch for relation-based filtering
     * (e.g. movements whereHas product in branch).
     */
    protected function scopeByProductBranch($query, ?string $branchIdFilter = null)
    {
        return $this->scopeByRelationBranch($query, 'product', $branchIdFilter);
    }
}
