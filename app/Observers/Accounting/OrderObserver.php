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
            // Auto-posting must never break the operational flow.
            try {
                $this->posting->postSale( $order );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (sale) failed: ' . $e->getMessage(), [ 'order_id' => $order->id ] );
            }
        }
    }
