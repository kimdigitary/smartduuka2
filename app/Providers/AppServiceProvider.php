<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(Request $request): void
    {
//            info(Hash::make( 'Admin@support12'));
//            Model::preventLazyLoading();
//            Illuminate\Support\Facades\Hash::make('Admin@support12');

        require_once app_path('Helpers/functions.php');

        Model::saving(function (Model $model): void {
            $request = request();

            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'], TRUE)) {
                return;
            }

            $branchId = $request->header('X-BranchId');

            if ($branchId === NULL || !ctype_digit(trim((string)$branchId))) {
                return;
            }

            static $tablesWithBranchId = [];

            $table = $model->getTable();
            $key = $model->getConnection()->getName() . '.' . $table;

            if (!array_key_exists($key, $tablesWithBranchId)) {
                $tablesWithBranchId[$key] = $model->getConnection()->getSchemaBuilder()->hasColumn($table, 'branch_id');
            }

            if (!$tablesWithBranchId[$key]) {
                return;
            }

            $model->forceFill([
                'branch_id' => (int)$branchId,
            ]);
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
