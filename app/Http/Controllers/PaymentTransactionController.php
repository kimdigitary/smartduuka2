<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaymentTransactionRequest;
    use App\Http\Resources\PaymentTransactionResource;
    use App\Models\PaymentTransaction;

    class PaymentTransactionController extends Controller
    {
        public function index()
        {
            return PaymentTransactionResource::collection( PaymentTransaction::all() );
        }

        public function store(PaymentTransactionRequest $request)
        {
            return new PaymentTransactionResource( PaymentTransaction::create( $request->validated() ) );
        }

        public function show(PaymentTransaction $paymentTransaction)
        {
            return new PaymentTransactionResource( $paymentTransaction );
        }

        public function update(PaymentTransactionRequest $request , PaymentTransaction $paymentTransaction)
        {
            $paymentTransaction->update( $request->validated() );

            return new PaymentTransactionResource( $paymentTransaction );
        }

        public function destroy(PaymentTransaction $paymentTransaction)
        {
            $paymentTransaction->delete();

            return response()->json();
        }
    }
