<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\CategoryRequest;
    use App\Http\Resources\Accounting\CategoryResource;
    use IFRS\Models\Category;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class CategoryController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return CategoryResource::collection( Category::orderBy( 'name' )->get() );
        }

        public function store(CategoryRequest $request) : CategoryResource
        {
            return new CategoryResource( $this->fill( new Category(), $request->validated() ) );
        }

        public function update(CategoryRequest $request, int $id) : CategoryResource
        {
            return new CategoryResource( $this->fill( Category::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Category::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Category $category, array $data) : Category
        {
            $category->name          = $data[ 'name' ];
            $category->category_type = $data[ 'categoryType' ];
            $category->save();

            return $category;
        }
    }
