<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PrintDesignRequest;
    use App\Http\Resources\PrintDesignResource;
    use App\Models\PrintDesign;

    class PrintDesignController extends Controller
    {
        public function index()
        {
            return PrintDesignResource::collection( PrintDesign::all() );
        }

        public function store(PrintDesignRequest $request)
        {
            return new PrintDesignResource( PrintDesign::create( $request->validated() ) );
        }

        public function show(PrintDesign $printDesign)
        {
            return new PrintDesignResource( $printDesign );
        }

        public function update(PrintDesignRequest $request , PrintDesign $printDesign)
        {
            $printDesign->update( $request->validated() );

            return new PrintDesignResource( $printDesign );
        }

        public function destroy(PrintDesign $printDesign)
        {
            $printDesign->delete();

            return response()->json();
        }
    }
