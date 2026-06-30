<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * A posting that could not reach the ledger (currently: closed-period failures).
     * Surfaced to the user so they can post a manual adjusting entry.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property string      $source
     * @property string|null $source_id
     * @property string|null $posting_date
     * @property string|null $narration
     * @property string|null $message
     * @property string|null $resolved_at
     */
    class PostingAlert extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_posting_alerts';

        protected $fillable = [
            'entity_id',
            'source',
            'source_id',
            'posting_date',
            'narration',
            'message',
            'resolved_at',
        ];

        protected $casts = [
            'posting_date' => 'date',
            'resolved_at'  => 'datetime',
        ];
    }
