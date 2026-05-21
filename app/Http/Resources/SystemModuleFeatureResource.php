<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SystemModuleFeatureResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'      => $this->id ,
                'name'    => $this->name ,
                'enabled' => (bool) ( $this->enabled ) ,
            ];
        }
    }