<?php

    namespace Database\Seeders;

    use App\Enums\DesignStyle;
    use App\Enums\TemplateType;
    use App\Models\PrintDesign;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;

    class PrintDesignSeeder extends Seeder
    {
        public function run() : void
        {
            if ( DB::table( 'print_designs' )->exists() ) {
                return;
            }
            $designs = [
                [
                    'style'           => DesignStyle::CLASSIC ,
                    'name'            => 'Classic Structure' ,
                    'description'     => 'Standard layout with traditional alignment.' ,
                    'recommendations' => [ TemplateType::THERMAL , TemplateType::A4 ] ,
                ] ,
                [
                    'style'           => DesignStyle::MODERN ,
                    'name'            => 'Modern Minimal' ,
                    'description'     => 'Clean lines, whitespace, bold headers.' ,
                    'recommendations' => [ TemplateType::THERMAL , TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::MINIMAL ,
                    'name'            => 'Eco Saver' ,
                    'description'     => 'Compact layout designed to save printer ink.' ,
                    'recommendations' => [ TemplateType::THERMAL ] ,
                ] ,
                [
                    'style'           => DesignStyle::BORDERED_TABLE ,
                    'name'            => 'Bordered Table' ,
                    'description'     => 'Distinct borders separating table columns and rows.' ,
                    'recommendations' => [ TemplateType::THERMAL , TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::BOLD_COMPACT ,
                    'name'            => 'Bold Compact' ,
                    'description'     => 'Thick fonts and tight spacing for small prints.' ,
                    'recommendations' => [ TemplateType::THERMAL ] ,
                ] ,
                [
                    'style'           => DesignStyle::PROFESSIONAL_BLUE ,
                    'name'            => 'Professional Blue' ,
                    'description'     => 'A4 design with blue accents for invoices.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::GRID_SYSTEM ,
                    'name'            => 'Grid System' ,
                    'description'     => 'Everything neatly packed into geometric grids.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::HEADER_FOCUS ,
                    'name'            => 'Header Focus' ,
                    'description'     => 'Large prominent branding at the top.' ,
                    'recommendations' => [ TemplateType::THERMAL , TemplateType::A4 ] ,
                ] ,
                [
                    'style'           => DesignStyle::ELEGANT_SERIF ,
                    'name'            => 'Elegant Serif' ,
                    'description'     => 'Serif fonts for luxury shops or boutiques.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::INDUSTRIAL_MONO ,
                    'name'            => 'Industrial Mono' ,
                    'description'     => 'Typewriter style for hardware stores.' ,
                    'recommendations' => [ TemplateType::THERMAL , TemplateType::A4 ] ,
                ] ,
                [
                    'style'           => DesignStyle::HIGH_CONTRAST ,
                    'name'            => 'High Contrast' ,
                    'description'     => 'Maximized black and white space for older printers.' ,
                    'recommendations' => [ TemplateType::THERMAL ] ,
                ] ,
                [
                    'style'           => DesignStyle::OCEAN_WAVE_COLOR ,
                    'name'            => 'Ocean Wave Color' ,
                    'description'     => 'Cyan and deep blue colored landscape.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::GREEN_LEAF_COLOR ,
                    'name'            => 'Green Leaf Color' ,
                    'description'     => 'Eco-friendly green accented design.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::CRIMSON_LUXURY_COLOR ,
                    'name'            => 'Crimson Luxury' ,
                    'description'     => 'Deep red accents for premium receipts.' ,
                    'recommendations' => [ TemplateType::A4 , TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::SUMMARY_REPORT ,
                    'name'            => 'Summary Report' ,
                    'description'     => 'Optimized for wide totals and aggregated data.' ,
                    'recommendations' => [ TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::DETAILED_ANALYTICS ,
                    'name'            => 'Detailed Analytics' ,
                    'description'     => 'Landscape table-heavy design for big data.' ,
                    'recommendations' => [ TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::GRAPH_FOCUS ,
                    'name'            => 'Graph Focus' ,
                    'description'     => 'Space reserved for charts and high-level metrics.' ,
                    'recommendations' => [ TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::INVENTORY_AUDIT ,
                    'name'            => 'Inventory Audit' ,
                    'description'     => 'Wide columns for stock taking and variances.' ,
                    'recommendations' => [ TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::SALES_VIBE ,
                    'name'            => 'Sales Vibe' ,
                    'description'     => 'Energetic design highlighting total revenue and growth.' ,
                    'recommendations' => [ TemplateType::REPORT ] ,
                ] ,
                [
                    'style'           => DesignStyle::ECO_SAVER ,
                    'name'            => 'Ultra Eco Saver' ,
                    'description'     => 'Zero borders, minimal padding, small text.' ,
                    'recommendations' => [ TemplateType::THERMAL ] ,
                ] ,
            ];

            foreach ( $designs as $design ) {
                PrintDesign::create( $design );
            }
        }
    }
