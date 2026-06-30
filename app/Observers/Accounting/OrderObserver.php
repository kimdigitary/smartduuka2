<?php

    namespace App\Observers\Accounting;

    use App\Models\Order;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class OrderObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(Order $order) : void
        {
            $this->sync( $order, 'create' );
        }

        /**
         * Edits, quotation→sale conversion and payment changes all flow through an
         * Order update — postSale reconciles the ledger to the new state (delta only).
         */
        public function updated(Order $order) : void
        {
            $this->sync( $order, 'update' );
        }

        public function deleted(Order $order) : void
        {
            try {
                $this->posting->voidSale( $order );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting void (sale) failed: ' . $e->getMessage(), [ 'order_id' => $order->id ] );
            }
        }

        private function sync(Order $order, string $context) : void
        {
            // Auto-posting must never break the operational flow.
            try {
                $this->posting->postSale( $order );
            } catch ( \Throwable $e ) {
                Log::error( "Accounting auto-post (sale {$context}) failed: " . $e->getMessage(), [ 'order_id' => $order->id ] );
            }
        }
    }
