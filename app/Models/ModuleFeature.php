<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class ModuleFeature extends Model
    {
        protected $fillable = [
            'name' ,
            'enabled' ,
        ];

        protected function casts() : array
        {
            return [
                'enabled' => 'boolean' ,
            ];
        }
    }
