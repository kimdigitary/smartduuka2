<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class TemplateType extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'name' ,
            'description' ,
            'icon' ,
        ];
    }
