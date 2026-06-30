<?php

    namespace App\Observers\Accounting;

    use App\Models\Damage;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class DamageObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(Damage $damage) : void
        {
            $this->sync( $damage );
        }

        public function updated(Damage $damage) : void
        {
            $this->sync( $damage );
        }

        public function deleted(Damage $damage) : void
        {
            try {
                $this->posting->voidDamage( $damage );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting void (damage) failed: ' . $e->getMessage(), [ 'damage_id' => $damage->id ] );
            }
        }

        private function sync(Damage $damage) : void
        {
            try {
                $this->posting->postDamage( $damage );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (damage) failed: ' . $e->getMessage(), [ 'damage_id' => $damage->id ] );
            }
        }
    }
