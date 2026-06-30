<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * A business loan / borrowing. Drawdown and repayment post IFRS journals from
     * the frontend; this record tracks the outstanding principal and status.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property string      $name
     * @property string|null $lender
     * @property string|null $reference
     * @property float       $principal
     * @property float       $interest_rate
     * @property string      $method
     * @property string      $start_date
     * @property int         $term_months
     * @property string      $frequency
     * @property int         $liability_account_id
     * @property float       $outstanding_principal
     * @property string      $status
     * @property string|null $branch_id
     */
    class Loan extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_loans';

        protected $fillable = [
            'entity_id',
            'name',
            'lender',
            'reference',
            'principal',
            'interest_rate',
            'method',
            'start_date',
            'term_months',
            'frequency',
            'liability_account_id',
            'outstanding_principal',
            'status',
            'branch_id',
        ];

        protected $casts = [
            'principal'             => 'decimal:4',
            'interest_rate'         => 'decimal:4',
            'outstanding_principal' => 'decimal:4',
            'term_months'           => 'integer',
            'start_date'            => 'date',
        ];
    }
