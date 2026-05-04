<?php

    namespace App\Http\Resources;

    use App\Models\TemplateType;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin TemplateType */
    class TemplateTypeResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'description' => $this->description ,
                'icon'        => $this->icon ,
            ];
        }
    }
