<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\Category;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Category */
    class CategoryResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'           => $this->id,
                'name'         => $this->name,
                'categoryType' => $this->category_type,
            ];
        }
    }
