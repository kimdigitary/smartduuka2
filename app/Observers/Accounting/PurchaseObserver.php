<?php

    namespace App\Observers\Accounting;

    use App\Models\Purchase;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class PurchaseObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(Purchase $purchase) : void
        {
            $this->sync( $purchase );
        }

        public function updated(Purchase $purchase) : void
        {
            $this->sync( $purchase );
        }

        public function deleted(Purchase $purchase) : void
        {
            try {
                $this->posting->voidPurchase( $purchase );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting void (purchase) failed: ' . $e->getMessage(), [ 'purchase_id' => $purchase->id ] );
            }
        }

        private function sync(Purchase $purchase) : void
        {
            try {
                $this->posting->postPurchase( $purchase );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (purchase) failed: ' . $e->getMessage(), [ 'purchase_id' => $purchase->id ] );
            }
        }
    }
