<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Models\PaymentMethod;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;

    /**
     * Maps each operational payment method (Cash, Mobile Money, Card, Bank…) to the
     * ledger account that should be debited/credited when it settles a sale or
     * payment. Consumed by OperationalPostingService::methodAccount(); an unmapped
     * method falls back to the default bank account.
     *
     * Stored as a single { "<paymentMethodId>": <accountId> } blob per tenant.
     */
    class PaymentMethodAccountController extends Controller
    {
        private const KEY = 'payment_method_accounts';

        public function index() : JsonResponse
        {
            $map = Settings::group( 'accounting' )->get( self::KEY ) ?: [];

            $methods = PaymentMethod::query()
                                    ->withoutGlobalScopes()
                                    ->orderBy( 'name' )
                                    ->get( [ 'id', 'name' ] )
                                    ->map( static fn (PaymentMethod $m) => [
                                        'id'        => $m->id,
                                        'name'      => $m->name,
                                        'accountId' => isset( $map[ (string) $m->id ] ) ? (int) $map[ (string) $m->id ] : NULL,
                                    ] )
                                    ->values();

            return response()->json( [
                'data' => [
                    'map'     => (object) $map,
                    'methods' => $methods,
                ],
            ] );
        }

        public function store(Request $request) : JsonResponse
        {
            // The frontend posts the whole map as a JSON string under `map`.
            $incoming = $request->input( 'map' );
            if ( is_string( $incoming ) ) {
                $incoming = json_decode( $incoming, TRUE ) ?: [];
            }

            $map = [];
            foreach ( (array) $incoming as $methodId => $accountId ) {
                if ( $accountId !== NULL && $accountId !== '' ) {
                    $map[ (string) $methodId ] = (int) $accountId;
                }
            }

            Settings::group( 'accounting' )->set( [ self::KEY => $map ] );

            return response()->json( [ 'data' => $map ] );
        }
    }
