<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Spatie\MediaLibrary\HasMedia;
    #[ScopedBy( [ BranchScope::class ] )]
    class ServiceCategory extends Model implements HasMedia
    {
        use HasImageMedia;

        protected $fillable = [
            'name' ,
            'description' ,
        ];
    }
