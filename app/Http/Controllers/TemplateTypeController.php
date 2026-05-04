<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\TemplateTypeRequest;
    use App\Http\Resources\TemplateTypeResource;
    use App\Models\TemplateType;

    class TemplateTypeController extends Controller
    {
        public function index()
        {
            return TemplateTypeResource::collection( TemplateType::all() );
        }

        public function store(TemplateTypeRequest $request)
        {
            return new TemplateTypeResource( TemplateType::create( $request->validated() ) );
        }

        public function show(TemplateType $templateType)
        {
            return new TemplateTypeResource( $templateType );
        }

        public function update(TemplateTypeRequest $request , TemplateType $templateType)
        {
            $templateType->update( $request->validated() );

            return new TemplateTypeResource( $templateType );
        }

        public function destroy(TemplateType $templateType)
        {
            $templateType->delete();

            return response()->json();
        }
    }
