<?php

    namespace Database\Seeders;

    use App\Enums\Status;
    use App\Models\Branch;
    use Illuminate\Database\Seeder;
    use Smartisan\Settings\Facades\Settings;

    class BranchSeeder extends Seeder
    {
        public function run() : void
        {
            $branch = Branch::firstOrCreate( [ 'name' => 'Main Branch' ] , [
                'name'    => 'Main Branch' ,
                'code'    => 'BR-' ,
                'manager' => 'Manager' ,
                'phone'   => '0701234567' ,
                'email'   => "branch@example.com" ,
                'status'  => Status::ACTIVE ,
                'address' => 'Kampala' ,
            ] );
            $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );
            Settings::group( 'site' )->set( [ 'site_default_branch' => $branch->id ] );
        }
    }
