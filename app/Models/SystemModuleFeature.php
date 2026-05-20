<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class SystemModuleFeature extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'system_module_id' ,
            'module_feature_id' ,
        ];

        public function systemModule() : BelongsTo
        {
            return $this->belongsTo( SystemModule::class );
        }

        public function moduleFeature() : BelongsTo
        {
            return $this->belongsTo( ModuleFeature::class );
        }
    }
