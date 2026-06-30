<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;

    /**
     * The "default accounts" mapping links our operational posting keys
     * (bank, salesRevenue, cogs, vatOutput, fxGainLoss, pettyCash, …) to IFRS
     * account ids. Stored as a single settings blob per tenant.
     */
    class DefaultAccountController extends Controller
    {
        private const KEY = 'default_accounts';

        public function index() : JsonResponse
        {
            return response()->json( [
                'data' => Settings::group( 'accounting' )->get( self::KEY ) ?: (object) [],
            ] );
        }

        public function store(Request $request) : JsonResponse
        {
            // Accept the whole key => accountId map the frontend posts.
            $map = array_filter(
                $request->except( [ '_method' ] ),
                static fn ($value) => $value !== NULL && $value !== '',
            );

            Settings::group( 'accounting' )->set( [ self::KEY => $map ] );

            return response()->json( [ 'data' => $map ] );
        }
    }
