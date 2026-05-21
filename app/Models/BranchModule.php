<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class BranchModule extends Model
    {
        protected $table    = 'branch_modules';
        protected $fillable = [ 'branch_id' , 'system_module_id' , 'enabled' ];
        protected $casts    = [ 'enabled' => 'boolean' ];
    }
