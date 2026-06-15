<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class ModuleCategory extends Model
    {
        protected $fillable = [
            'name' ,
            'branch_id',
        ];

        public function modules() : HasMany
        {
            return $this->hasMany( SystemModule::class );
        }
    }