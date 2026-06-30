<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * Matches a clearing transaction (receipt/payment) against a clearable one
     * (invoice/bill) for `amount`. Aging = original − sum(assigned).
     *
     * @property int    $id
     * @property int    $entity_id
     * @property int    $transaction_id
     * @property int    $cleared_id
     * @property float  $amount
     * @property string $date
     */
    class Assignment extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_assignments';

        protected $fillable = [
            'entity_id',
            'transaction_id',
            'cleared_id',
            'amount',
            'date',
        ];

        protected $casts = [
            'amount' => 'decimal:4',
            'date'   => 'date',
        ];
    }
