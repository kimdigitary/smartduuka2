<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
    use Stancl\Tenancy\Contracts\SyncMaster;
    use Stancl\Tenancy\Database\Concerns\CentralConnection;
    use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
    use Stancl\Tenancy\Database\Models\TenantPivot;

    class CentralPersonalAccessToken extends SanctumToken implements SyncMaster
    {
        use ResourceSyncing , CentralConnection;

//        protected $guarded = [];

        protected $fillable = [
            'name',
            'token',
            'abilities',
            'expires_at',
            'last_used_at',
            'tokenable_type',
            'tokenable_id',
            'global_id',
        ];

        public $table = 'personal_access_tokens';

        // ──────────────────────────────────────────────
        //  SyncMaster: relationship to tenants
        // ──────────────────────────────────────────────

        public function tenants() : BelongsToMany
        {
            return $this->belongsToMany(
                Tenant::class ,
                'tenant_personal_access_tokens' ,
                'global_token_id' ,
                'tenant_id' ,
                'global_id' ,   // local key on this model
                'id'            // owner key on Tenant
            )->using( TenantPivot::class );
        }

        // ──────────────────────────────────────────────
        //  Sanctum: resolve tokenable against CentralUser
        // ──────────────────────────────────────────────

        public function tokenable() : MorphTo
        {
            return $this->morphTo( 'tokenable' , 'tokenable_type' , 'tokenable_id' );
        }

        // ──────────────────────────────────────────────
        //  Syncable / SyncMaster interface
        // ──────────────────────────────────────────────

        public function getTenantModelName() : string
        {
            return TenantPersonalAccessToken::class;
        }

        public function getGlobalIdentifierKey() : mixed
        {
            return $this->getAttribute( $this->getGlobalIdentifierKeyName() );
        }

        public function getGlobalIdentifierKeyName() : string
        {
            return 'global_id';
        }

        public function getCentralModelName() : string
        {
            return static::class;
        }

        public function getSyncedAttributeNames() : array
        {
            return [
                'tokenable_type' ,
                'tokenable_id' ,
                'name' ,
                'token' ,
                'abilities' ,
                'last_used_at' ,
                'expires_at' ,
            ];
        }
    }