<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class BusinessOnBoard extends Model
    {
        protected $fillable = [
            'name' ,
            'tenant' ,
            'email' ,
            'phone' ,
            'mobile_phone_number' ,
            'address' ,
            'admin_email' ,
            'admin_password' ,
            'admin_pin' ,
            'payment_method' ,
            'plan_id' ,
            'cycle_id' ,
            'amount' ,
            'admin_name' , 'status'
        ];
        protected $casts    = [ 'status' => Status::class ];

        protected function domain() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->tenant . config( 'session.domain' ) ,
            );
        }

        public function business() : HasOne
        {
            return $this->hasOne( Tenant::class , 'id' , 'tenant' );
        }
    }
