<?php

    namespace App\Models;

    use App\Enums\Status;
    use App\Helpers\JwtHelper;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class TenantBranch extends Model
    {
        protected $fillable = [
            'name' ,
            'email' ,
            'tenant_id' ,
            'website' ,
            'zip_code' ,
            'country' ,
            'city' ,
            'address' ,
            'phone' ,
            'phone2' ,
            'code' ,
            'status' , 'can_delete' , 'state' , 'share_customers' , 'share_wallets' , 'share_loyalty' , 'share_accounting' , 'share_reports' , 'share_procurement'
        ];

        protected $casts = [
            'status'            => Status::class ,
            'can_delete'        => 'boolean' ,
            'share_customers'   => 'boolean' ,
            'share_wallets'     => 'boolean' ,
            'share_loyalty'     => 'boolean' ,
            'share_accounting'  => 'boolean' ,
            'share_reports'     => 'boolean' ,
            'share_procurement' => 'boolean' ,
        ];

        public function tenant() : BelongsTo
        {
            return $this->belongsTo( Tenant::class );
        }

        public function scopeTenantBranch(Builder $query , string $tenantId , string | int $branchId) : Builder
        {
            return $query->where( 'tenant_id' , $tenantId )
                         ->where( 'id' , $branchId );
        }

        public function scopeForTenant(Builder $query , string $tenantId) : Builder
        {
            return $query->where( 'tenant_id' , $tenantId );
        }

        public function token() : Attribute
        {
            return new Attribute( get: fn() => JwtHelper::sign( [ 'branchId' => $this->id ] ) );
        }
    }