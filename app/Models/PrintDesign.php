<?php

    namespace App\Models;

    use App\Enums\DesignStyle;
    use Illuminate\Database\Eloquent\Model;

    class PrintDesign extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'name' ,
            'style' ,
            'description' ,
            'recommendations' ,
        ];
        protected $casts    = [ 'style' => DesignStyle::class , 'recommendations' => 'array' ];
    }
