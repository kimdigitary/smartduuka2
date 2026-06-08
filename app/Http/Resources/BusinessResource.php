<?php

    namespace App\Http\Resources;

    use App\Models\BusinessOnBoard;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin BusinessOnBoard
     */
    class BusinessResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => $this->id ,
                'name'                => $this->name ,
                'email'               => $this->email ,
                'phone'               => $this->phone ,
                'mobile_phone_number' => $this->mobile_phone_number ,
                'address'             => $this->address ,
                'admin_email'         => $this->admin_email ,
                'admin_name'          => $this->admin_name ,
                'payment_method'      => $this->payment_method ,
                'plan_id'             => $this->plan_id ,
                'cycle_id'            => $this->cycle_id ,
                'amount'              => $this->amount ,
                'status'              => $this->status ,
                'domain'              => $this->domain ,
                'tenant'              => new TenantResource( $this->whenLoaded( 'business' ) ) ,
                'created_at'          => $this->created_at ? $this->created_at->format( 'Y-m-d H:i:s' ) : NULL ,
                'updated_at'          => $this->updated_at ? $this->updated_at->format( 'Y-m-d H:i:s' ) : NULL ,
            ];
        }
    }
