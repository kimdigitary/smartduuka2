<?php

    namespace App\Http\Resources;


    use App\Models\TenantBranch;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin TenantBranch
     */
    class BranchResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                "id"         => $this->id ,
                "name"       => $this->name ,
                "email"      => $this->email === NULL ? '' : $this->email ,
                "phone"      => $this->phone === NULL ? '' : $this->phone ,
                "address"    => $this->address ,
                "staffCount" => 0 ,
                "code"       => $this->code ,
                "can_delete" => $this->can_delete ,
                "status"     => $this->status ,
                "c"          => $this->status
            ];
        }
    }
