<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class BranchModuleFeature extends Model
    {
        protected $table    = 'branch_module_feature';
        protected $fillable = [ 'branch_id' , 'module_feature_id' , 'enabled' ];
        protected $casts    = [ 'enabled' => 'boolean' ];
    }
