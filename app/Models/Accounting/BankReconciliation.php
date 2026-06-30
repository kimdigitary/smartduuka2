<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * Per-bank-account reconciliation state: the statement balance entered by the
     * user and the set of ledger transaction ids ticked as cleared.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property int         $account_id
     * @property float       $statement_balance
     * @property array       $cleared_txn_ids
     * @property string|null $last_reconciled_date
     */
    class BankReconciliation extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_bank_reconciliations';

        protected $fillable = [
            'entity_id',
            'account_id',
            'statement_balance',
            'cleared_txn_ids',
            'last_reconciled_date',
        ];

        protected $casts = [
            'statement_balance'    => 'decimal:4',
            'cleared_txn_ids'      => 'array',
            'last_reconciled_date' => 'date',
        ];
    }
