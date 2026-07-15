<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;

/**
 * Single seam for future multi-tenant scoping. Every repository query passes
 * through here. Today it is a no-op (single-tenant app) — when client
 * companies get their own logins, this becomes the one place a
 * ->where('company_id', ...) constraint gets added, instead of hunting
 * through every repository/controller in the app.
 */
class TenantScope
{
    public function apply(Builder $query): Builder
    {
        return $query;
    }
}
