<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\BankReconciliationRequest;
    use App\Models\Accounting\BankReconciliation;
    use Illuminate\Http\JsonResponse;

    /**
     * Bank reconciliation state, keyed by bank account id. The frontend keeps it as
     * a Record<accountId, {statementBalance, clearedTxnIds, lastReconciledDate}> so
     * index returns that exact map and store upserts one account at a time.
     */
    class BankReconciliationController extends Controller
    {
        public function index() : JsonResponse
        {
            $map = BankReconciliation::all()->mapWithKeys( static fn (BankReconciliation $r) => [
                (string) $r->account_id => [
                    'statementBalance'   => (float) $r->statement_balance,
                    'clearedTxnIds'      => array_map( 'strval', $r->cleared_txn_ids ?? [] ),
                    'lastReconciledDate' => optional( $r->last_reconciled_date )->toDateString(),
                ],
            ] );

            return response()->json( [ 'data' => (object) $map->all() ] );
        }

        public function store(BankReconciliationRequest $request) : JsonResponse
        {
            $data = $request->validated();

            $reconciliation = BankReconciliation::firstOrNew( [
                'account_id' => $data[ 'accountId' ],
            ] );
            $reconciliation->statement_balance    = $data[ 'statementBalance' ] ?? 0;
            $reconciliation->cleared_txn_ids       = array_values( array_map( 'strval', $data[ 'clearedTxnIds' ] ?? [] ) );
            $reconciliation->last_reconciled_date  = $data[ 'lastReconciledDate' ] ?? NULL;
            $reconciliation->save();

            return response()->json( [ 'data' => [
                'accountId'          => (string) $reconciliation->account_id,
                'statementBalance'   => (float) $reconciliation->statement_balance,
                'clearedTxnIds'      => $reconciliation->cleared_txn_ids,
                'lastReconciledDate' => optional( $reconciliation->last_reconciled_date )->toDateString(),
            ] ] );
        }
    }
