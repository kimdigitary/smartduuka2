<?php

    namespace App\Models\Scopes;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Scope;


    class TenantBranchScope implements Scope
    {
        public function apply(Builder $builder , Model $model) : void
        {
            $branch_id = request( 'branch_id' );

            if ( ! $branch_id || $branch_id == 0 ) {
                return;
            }

            $field = sprintf( '%s.%s' , $builder->getQuery()->from , 'branch_id' );
            $builder->where( $field , $branch_id );
        }
    }
