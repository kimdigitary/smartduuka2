<?php

    namespace Database\Seeders;

    use App\Models\TemplateType;
    use Illuminate\Database\Seeder;

    class PrintTemplateSeeder extends Seeder
    {
        public function run() : void
        {
            if ( TemplateType::query()->exists() ) {
                return;
            }

            $templates = [
                [
                    'name'        => 'Thermal Receipt' ,
                    'icon'        => 'FaReceipt' ,
                    'description' => 'Standard 80mm/58mm roll for POS printers.' ,
                ] ,
                [
                    'name'        => 'A4 Invoice' ,
                    'icon'        => 'FaFileInvoice' ,
                    'description' => 'Full-size document for B2B or large customers.' ,
                ] ,
                [
                    'name'        => 'Repair / Service Ticket' ,
                    'icon'        => 'FaWrench' ,
                    'description' => 'Focuses on service tracking without prices.' ,
                ] ,
                [
                    'name'        => 'System Report' ,
                    'icon'        => 'FaChartBar' ,
                    'description' => 'Analytics and Inventory tracking formats.' ,
                ] ,
            ];

            TemplateType::query()->insert( $templates );
        }
    }
