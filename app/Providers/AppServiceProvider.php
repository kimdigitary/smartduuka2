<?php

    namespace App\Providers;

    use Illuminate\Auth\Notifications\ResetPassword;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        public function boot(Request $request) : void
        {
//            info(Hash::make( 'Admin@support12'));
//            Model::preventLazyLoading();
//            Illuminate\Support\Facades\Hash::make('Admin@support12');

            require_once app_path( 'Helpers/functions.php' );
            ResetPassword::createUrlUsing( function (object $notifiable , string $token) {
                return config( 'app.frontend_url' ) . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
            } );
        }
    }
