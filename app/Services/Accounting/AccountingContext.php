<?php

    namespace App\Services\Accounting;

    use App\Models\User;
    use IFRS\Models\Entity;
    use Illuminate\Support\Facades\Auth;

    /**
     * Ensures an IFRS entity is active for the current execution so accounting
     * models segregate correctly. In an HTTP request the authenticated user
     * already carries entity_id (shared at bootstrap); for CLI/queue/backfill
     * contexts we authenticate as an entity user.
     */
    class AccountingContext
    {
        public static function ensure() : ?Entity
        {
            $entity = Entity::query()->first();
            if ( ! $entity ) {
                return NULL;
            }

            $user = Auth::user();
            if ( ! $user || ! $user->entity_id ) {
                $actor = User::query()->where( 'entity_id', $entity->id )->first();
                if ( $actor ) {
                    Auth::setUser( $actor );
                }
            }

            return $entity;
        }
    }
