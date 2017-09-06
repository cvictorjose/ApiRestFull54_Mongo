<?php

namespace App\Providers;

use App\Auth\CustomUserProvider;
use App\Comment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Laravel\Passport\Passport;

use App\Policies\GenrePolicy;
use App\Policies\StoryPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\UserPolicy;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
         \App\Genre::class  => GenrePolicy::class,
         \App\Membership::class  => MembershipPolicy::class,
         \App\Story::class => StoryPolicy::class,
         \App\User::class => UserPolicy::class,
         \App\Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
         $this->registerPolicies();


        Auth::provider('custom', function($app, array $config) {
            return new CustomUserProvider($app['hash'], $config['model']);
        });

         Passport::routes();
         Passport::tokensExpireIn(Carbon::now()->addDay(1));
         Passport::refreshTokensExpireIn(Carbon::now()->addDay(1));


        /**
         * Admin=true can do any tasks
         *
         * @param  \App\User  $user
         * @return true/false
         */
        $gate->before(function ($user) {
            foreach($user->role as $role)
            {
                if ($role === 'admin') {
                    return true;
                }
            }
        });
    }
}
