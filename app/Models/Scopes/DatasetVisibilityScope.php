<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class DatasetVisibilityScope implements Scope
{
    /**
     * Global scope, this is applied to all queries on the Dataset model
     * It filters out datasets that are not approved for all users and only public datasets for non-admin users
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        // For non-admin users, only show public datasets
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            $builder->where('is_public', true);
        }

        //$builder->where('is_approved', true);
    }

}
