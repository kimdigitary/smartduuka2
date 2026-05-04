<?php

    use App\Enums\DesignStyle;
    use App\Enums\PageOrientation;
    use App\Enums\ThermalSize;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'print_templates' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->foreignId( 'type' )->constrained( 'template_types' )->cascadeOnDelete();
                $table->unsignedTinyInteger( 'design' )->default( DesignStyle::MODERN );
                $table->boolean( 'is_default' )->default( FALSE );
                $table->string( 'store_name' );
                $table->string( 'address' );
                $table->unsignedTinyInteger( 'page_orientation' )->default( PageOrientation::Portrait->value );
                $table->string( 'phone' );
                $table->boolean( 'show_logo' )->default( TRUE );
                $table->unsignedInteger( 'logo_size' )->default( 50 );
                $table->boolean( 'show_quantity' )->default( TRUE );
                $table->boolean( 'show_price' )->default( TRUE );
                $table->boolean( 'show_tax' )->default( FALSE );
                $table->string( 'footer_message' );
                $table->boolean( 'show_barcode' )->default( FALSE );
                $table->string( 'terms' )->nullable();
                $table->unsignedTinyInteger( 'thermal_size' )->default( ThermalSize::FIFTY_EIGHT_MM->value );
                $table->boolean( 'has_borders' )->default( TRUE );
                $table->boolean( 'text_bold' )->default( FALSE );
                $table->boolean( 'large_text' )->default( FALSE );
                $table->string( 'color_theme' )->default( '#ea580c' );
                $table->string( 'secondary_color' )->default( '#ea580c' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'print_templates' );
        }
    };
