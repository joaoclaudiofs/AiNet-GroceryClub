<?php

namespace App\Providers;
use App\Models\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        View::composer('*', function ($view) {
            $totalUsers = User::query()->where('type','member')->count();
        $cart = session('cart', []);
        $cartCount = 0;
        foreach ($cart as $item) {
            $cartCount += $item['quantity'] ?? 1;
        }
        $view->with('cartCount', $cartCount)
            ->with('totalUsers', $totalUsers);
         });
    }
}
