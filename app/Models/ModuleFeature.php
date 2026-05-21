<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class ModuleFeature extends Model
    {
        protected $fillable = [
            'name' ,
            'system_module_id' ,
        ];

        public function systemModule() : BelongsTo
        {
            return $this->belongsTo( SystemModule::class );
        }
    }