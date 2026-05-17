<?php

    namespace App\Models\Scopes;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Scope;

    class BranchScope implements Scope
    {
//        use DefaultAccessModelTrait;

        public function apply(Builder $builder , Model $model) : void
        {
            $builder->where( 'branch_id' , request( 'branch_id' ) );
//            if ( ! App::runningInConsole() && Auth::check() ) {
//                $field = sprintf( '%s.%s' , $builder->getQuery()->from , 'branch_id' );
//                $builder->where( $field , '=' , $this->branch() )->orWhere( $field , '=' , 0 );
//            }
        }
    }
