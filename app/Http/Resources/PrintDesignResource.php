<?php

    namespace App\Http\Resources;

    use App\Models\PrintDesign;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin PrintDesign */
    class PrintDesignResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'              => $this->id ,
                'name'            => $this->name ,
                'style'           => $this->style ,
                'description'     => $this->description ,
                'recommendations' => $this->recommendations ,
            ];
        }
    }
