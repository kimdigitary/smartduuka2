<?php

    namespace App\Observers\Accounting;

    use App\Models\CommissionPayout;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class CommissionPayoutObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(CommissionPayout $payout) : void
        {
            try {
                $this->posting->postCommissionPayout( $payout );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (commission payout) failed: ' . $e->getMessage(), [ 'commission_payout_id' => $payout->id ] );
            }
        }

        public function deleted(CommissionPayout $payout) : void
        {
            try {
                $this->posting->reverse( [ 'commission_payout' ], $payout->id );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting reversal (commission payout) failed: ' . $e->getMessage(), [ 'commission_payout_id' => $payout->id ] );
            }
        }
    }
