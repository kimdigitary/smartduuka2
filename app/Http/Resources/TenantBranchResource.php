<?php

    namespace App\Http\Resources;

    use App\Models\TenantBranch;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin TenantBranch */
    class TenantBranchResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'name'       => $this->name ,
                'email'      => $this->email ,
                'website'    => $this->website ,
                'zip_code'   => $this->zip_code ,
                'country'    => $this->country ,
                'city'       => $this->city ,
                'state'      => $this->city ,
                'address'    => $this->address ,
                'phone'      => $this->phone ,
                'phone2'     => $this->phone2 ,
                'code'       => $this->code ,
                'status'     => $this->status ,
                'tenant_id'  => $this->tenant_id ,
                'can_delete' => $this->can_delete ,
                'token'      => $this->token ,
                'created_at' => $this->created_at ,
                'updated_at' => $this->updated_at ,

//                'tenant' => new TenantResource( $this->whenLoaded( 'tenant' ) ) ,
            ];
        }
    }
