<?php

    namespace App\Observers\Accounting;

    use App\Models\CustomerWalletTransaction;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class CustomerWalletTransactionObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(CustomerWalletTransaction $wallet) : void
        {
            try {
                $this->posting->postWallet( $wallet );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (wallet) failed: ' . $e->getMessage(), [ 'wallet_txn_id' => $wallet->id ] );
            }
        }
    }
