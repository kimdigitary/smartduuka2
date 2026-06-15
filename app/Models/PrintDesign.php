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
            'branch_id',
        ];
        protected $casts    = [ 'style' => DesignStyle::class , 'recommendations' => 'array' ];
    }
