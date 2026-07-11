<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ActiveAccountScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $activeAccountId = Session::get('active_account_id');

        if ($activeAccountId) {
            $builder->where('account_id', $activeAccountId);
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutActiveAccountScope', function (Builder $builder) {
            $builder->withoutGlobalScope(ActiveAccountScope::class);

            return $builder;
        });
    }
}
