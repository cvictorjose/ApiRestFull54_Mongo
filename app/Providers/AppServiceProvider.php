<?php

namespace App\Providers;


use Adaojunior\Passport\SocialUserResolverInterface;
use App\Mail\changeEmail;
use App\SocialUserResolver;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {

        /*User::created(function($user) {
            Mail::to($user)->send(new changeEmail($user));
        });*/

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SocialUserResolverInterface::class, SocialUserResolver::class);
    }
}
