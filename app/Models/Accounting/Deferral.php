<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * An accrual or prepayment recognised in equal slices across `months`. Each
     * recognition posts a journal (Dr expense / Cr balance-sheet account) and the
     * frontend advances recognized_amount until it reaches total_amount.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property string      $name
     * @property string      $kind
     * @property float       $total_amount
     * @property int         $expense_account_id
     * @property int         $balance_account_id
     * @property string      $start_date
     * @property int         $months
     * @property float       $recognized_amount
     * @property string|null $branch_id
     * @property string      $status
     */
    class Deferral extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_deferrals';

        protected $fillable = [
            'entity_id',
            'name',
            'kind',
            'total_amount',
            'expense_account_id',
            'balance_account_id',
            'start_date',
            'months',
            'recognized_amount',
            'branch_id',
            'status',
        ];

        protected $casts = [
            'total_amount'      => 'decimal:4',
            'recognized_amount' => 'decimal:4',
            'months'            => 'integer',
            'start_date'        => 'date',
        ];
    }
