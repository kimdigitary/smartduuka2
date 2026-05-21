<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SystemModuleResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'description' => $this->description ,
                'icon'        => $this->icon ,
                'price'       => $this->price ,
                'price_text'  => 'UGX ' . number_format($this->price) ,
                // Directly safely checks the pivot object without strict table name matching
                'enabled'     => $this->enabled ,
                'category'    => ModuleCategoryResource::make( $this->whenLoaded( 'moduleCategory' ) ) ,
                'features'    => SystemModuleFeatureResource::collection( $this->whenLoaded( 'features' ) ) ,
            ];
        }
    }