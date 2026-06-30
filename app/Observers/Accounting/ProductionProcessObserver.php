<?php

    namespace App\Observers\Accounting;

    use App\Models\ProductionProcess;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class ProductionProcessObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(ProductionProcess $process) : void
        {
            $this->maybePost( $process );
        }

        public function updated(ProductionProcess $process) : void
        {
            $this->maybePost( $process );
        }

        public function deleted(ProductionProcess $process) : void
        {
            try {
                $this->posting->voidProduction( $process );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting void (production) failed: ' . $e->getMessage(), [ 'production_process_id' => $process->id ] );
            }
        }

        /**
         * postProduction reconciles to the batch's current state: a completed batch
         * posts the cost reclass, a cancelled/reverted one reverses it. Safe to call
         * on every create/update — it only posts the delta.
         */
        private function maybePost(ProductionProcess $process) : void
        {
            try {
                $this->posting->postProduction( $process );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (production) failed: ' . $e->getMessage(), [ 'production_process_id' => $process->id ] );
            }
        }
    }
