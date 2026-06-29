<?php

    namespace App\Http\Controllers;

    use App\Enums\TemplateType;
    use App\Http\Requests\PrintTemplateRequest;
    use App\Http\Resources\PrintTemplateResource;
    use App\Models\PrintTemplate;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;

    class PrintTemplateController extends Controller
    {
        public function index(Request $request)
        {
            $page     = $request->integer( 'page' );
            $per_page = $request->integer( 'per_page' );
            return PrintTemplateResource::collection( PrintTemplate::with( [ 'templateType' , 'templateDesign' ] )
                                                                   ->latest()
                                                                   ->paginate( perPage: $per_page , page: $page ) );
        }

        public function store(PrintTemplateRequest $request)
        {
            $data = $request->validated();
            if ( $data[ 'is_default' ] ) {
                PrintTemplate::where( 'is_default' , TRUE )
                             ->where( 'type' , $data[ 'type' ] )
                             ->update( [ 'is_default' => FALSE ] );
            }
            $template = PrintTemplate::create( $data );

            $this->updatePrintSettings( $template , $data );

            return new PrintTemplateResource( $template );
        }

        public function show(PrintTemplate $printTemplate)
        {
            return new PrintTemplateResource( $printTemplate );
        }

        public function update(PrintTemplateRequest $request , PrintTemplate $printTemplate)
        {
            $data = $request->validated();
            if ( $data[ 'is_default' ] ) {
                PrintTemplate::where( 'is_default' , TRUE )
                             ->where( 'type' , $data[ 'type' ] )
                             ->where( 'id' , '<>' , $printTemplate->id )
                             ->update( [ 'is_default' => FALSE ] );
            }
            $printTemplate->update( $data );
//            $printTemplate->refresh();

            $this->updatePrintSettings( $printTemplate , $data );

            return new PrintTemplateResource( $printTemplate );
        }

        public function destroy(Request $request)
        {
            // Clear the active printing slot for any default template being removed,
            // otherwise GET /printing keeps serving a deleted template.
            $templates = PrintTemplate::whereIn( 'id' , (array) $request->ids )->get();
            foreach ( $templates as $template ) {
                if ( $template->is_default ) {
                    $key = $this->settingKey( (int) $template->type );
                    if ( $key ) {
                        Settings::group( 'printing' )->set( [ $key => NULL ] );
                    }
                }
            }

            PrintTemplate::destroy( $request->ids );
            return response()->json();
        }

        private function updatePrintSettings(PrintTemplate $template , array $data) : void
        {
            if ( ! $template->is_default ) {
                return;
            }

            $key = $this->settingKey( (int) $template->type );
            if ( $key ) {
                Settings::group( 'printing' )->set( [ $key => $data ] );
            }
        }

        /** Map a template type id to its printing-settings key (Thermal / A4 / Report). */
        private function settingKey(int $type) : ?string
        {
            return match ( $type ) {
                TemplateType::THERMAL->value => 'Thermal' ,
                TemplateType::A4->value      => 'A4' ,
                TemplateType::REPORT->value  => 'Report' ,
                default                      => NULL ,
            };
        }
    }
