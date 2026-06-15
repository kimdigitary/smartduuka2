<?php

use App\Http\Middleware\AddCurrencySymbol;
use App\Http\Middleware\AddTenantIDAndBranchID;
use App\Http\Middleware\AfterMiddleware;
use App\Http\Middleware\CheckActiveRegister;
use App\Http\Middleware\CheckProductLimit;
use App\Http\Middleware\CheckSalesLimit;
use App\Http\Middleware\CheckUsersLimit;
use App\Http\Middleware\DetectUnusualLogin;
use App\Http\Middleware\DynamicSanctumConfiguration;
use App\Http\Middleware\EnsureBranchIdHeader;
use App\Http\Middleware\ForceAdminLogin;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RequireFeature;
use App\Http\Middleware\SubscribedMiddleware;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['api'])
                ->name('auth-api.')
                ->prefix('api')
                ->group(base_path('routes/auth-api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/adam.php'));
        },
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        [
            'prefix'     => 'api',
            'middleware' => [
                'api',
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                'auth:sanctum'
            ],
        ]
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->priority([
            HandlePrecognitiveRequests::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            EnsureFrontendRequestsAreStateful::class,
            DynamicSanctumConfiguration::class,
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
            SubstituteBindings::class,
            AuthenticatesRequests::class,
            Authorize::class,
        ]);
        $middleware->alias([
            'subscribed'      => SubscribedMiddleware::class,
            'permission'      => PermissionMiddleware::class,
            'local.auth'      => ForceAdminLogin::class,
            'register'        => CheckActiveRegister::class,
            'afterMiddleware' => AfterMiddleware::class,
            'feature'         => RequireFeature::class,
            'sales.limit'     => CheckSalesLimit::class,
            'users.limit'     => CheckUsersLimit::class,
            'items.limit'     => CheckProductLimit::class,
            'verify.branchid' => EnsureBranchIdHeader::class,
            'dynamic.sanctum' => DynamicSanctumConfiguration::class,
            'branchid'        => EnsureBranchIdHeader::class,
        ]);
        $middleware->append([
            AddCurrencySymbol::class,
            AfterMiddleware::class,
            AddTenantIDAndBranchID::class
        ]);
        $middleware->appendToGroup('web', DetectUnusualLogin::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->report(function (Throwable $e) {
            $trace = collect($e->getTrace())
                ->filter(fn($frame) => isset($frame['file']) &&
                    str_starts_with($frame['file'], base_path('app'))
                )
                ->map(fn($frame) => [
                    'file'     => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $frame['file']),
                    'line'     => $frame['line'],
                    'function' => $frame['function'],
                ])
                ->values()
                ->toArray();

            \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'trace'     => $trace,
            ]);

            return FALSE;
        });

        $exceptions->render(function (Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 403,
                'message' => ['message' => 'User does not have the right permissions.', 'details' => $e->getMessage()]
            ], 403);
        });

        $exceptions->render(function (TenantCouldNotBeIdentifiedException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 400,
                'message' => ['message' => 'Tenant error', 'details' => $e->getMessage()]
            ], 400);
        });

        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 404,
                'message' => ['message' => 'No query results for model.', 'details' => $e->getMessage()]
            ], 404);
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 405,
                'message' => ['message' => 'Method not supported for the route.', 'details' => $e->getMessage()]
            ], 405);
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 404,
                'message' => ['message' => 'The specified URL cannot be found.', 'details' => $e->getMessage()]
            ], 404);
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => $e->getStatusCode(),
                'message' => ['message' => 'Http exception', 'details' => $e->getMessage()]
            ], $e->getStatusCode());
        });

        $exceptions->render(function (Illuminate\Database\QueryException $e, $request) {
            return response()->json([
                'success' => FALSE,
                'status'  => 500,
                'message' => ['message' => 'Query exception', 'details' => $e->getMessage()]
            ], 500);
        });

    })->create();
