<?php

    namespace App\Models;

    use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
    use Stancl\Tenancy\Contracts\Syncable;
    use Stancl\Tenancy\Database\Concerns\ResourceSyncing;


    class TenantPersonalAccessToken extends SanctumToken implements Syncable
    {
        use ResourceSyncing;

//        protected $guarded = [];
        public    $table   = 'personal_access_tokens';

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
            return CentralPersonalAccessToken::class;
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
