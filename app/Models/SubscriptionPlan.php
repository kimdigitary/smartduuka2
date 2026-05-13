<?php

    namespace App\Models;

    use App\Enums\SubscriptionPlanType;
    use Illuminate\Database\Eloquent\Model;

    class SubscriptionPlan extends Model
    {
        protected $fillable = [
            'name' ,
            'description' ,
            'features' ,
            'base_amount' ,
            'popular' , 'type' , 'setup' , 'position'
        ];
        protected $casts    = [ 'popular' => 'boolean' , 'features' => 'array' , 'type' => SubscriptionPlanType::class , 'setup' => 'integer' ];

//        public function hasFeature(string $feature) : bool
//        {
//            return (bool) ( $this->features[ $feature ] ?? FALSE );
//        }
        public function hasFeature(string | \BackedEnum $feature) : bool
        {
            $key = $feature instanceof \BackedEnum ? $feature->value : $feature;
            return (bool) ( $this->features[ $key ] ?? FALSE );
        }
    }