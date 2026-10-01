<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', $request->user()?->locale ?? 'id');
        app()->setLocale(in_array($locale, ['id', 'en']) ? $locale : 'id');
        if ($request->user() && ! $request->user()->is_active) {
            auth()->logout();
            abort(403);
        }

        return $next($request);
    }
}
