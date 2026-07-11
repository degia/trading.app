<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $activeAccountId = Session::get('active_account_id');

        if ($activeAccountId) {
            $account = Auth::user()->accounts()->where('id', $activeAccountId)->first();

            if ($account) {
                Session::put('active_account_type', $account->type);
                view()->share('activeAccount', $account);
                view()->share('activeAccountType', $account->type);

                return $next($request);
            }
        }

        $account = Auth::user()->accounts()->first();

        if ($account) {
            Auth::user()->update(['active_account_id' => $account->id]);
            Session::put('active_account_type', $account->type);
            Session::put('active_account_id', $account->id);

            view()->share('activeAccount', $account);
            view()->share('activeAccountType', $account->type);

            return $next($request);
        }

        return $next($request);
    }
}
