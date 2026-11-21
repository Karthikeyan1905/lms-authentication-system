<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
        {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            // Super Admin can access all tenants
            if ($user->role === 'super_admin') {
                return;
            }

            // Deny access to tenant records if tenant_id is missing
            if (!$user->tenant_id) {
                $builder->whereRaw('1 = 0');
                return;
            }

            // Restrict users to their own tenant
            $builder->where(
                $model->getTable() . '.tenant_id',
                $user->tenant_id
            );
        }
}
