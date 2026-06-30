<?php

    namespace App\Models\Accounting\Concerns;

    use IFRS\Models\Entity;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Support\Facades\Auth;

    /**
     * Segregates an accounting extension model by IFRS entity, mirroring how the
     * eloquent-ifrs models scope themselves. In an HTTP request the authenticated
     * user carries entity_id (shared per tenant at bootstrap); otherwise we fall
     * back to the tenant's single entity. entity_id is auto-filled on create.
     */
    trait ScopedToEntity
    {
        public static function bootScopedToEntity() : void
        {
            static::addGlobalScope( 'entity', function (Builder $builder) {
                $entityId = self::currentEntityId();
                if ( $entityId ) {
                    $builder->where( $builder->getModel()->getTable() . '.entity_id', $entityId );
                }
            } );

            static::creating( function ($model) {
                if ( empty( $model->entity_id ) ) {
                    $model->entity_id = self::currentEntityId();
                }
            } );
        }

        protected static function currentEntityId() : ?int
        {
            $id = Auth::user()?->entity_id;
            if ( $id ) {
                return (int) $id;
            }

            return Entity::query()->first()?->id;
        }
    }
