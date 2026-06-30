<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\TransactionRequest;
    use App\Http\Resources\Accounting\TransactionResource;
    use App\Services\Accounting\PostingService;
    use IFRS\Models\Transaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class TransactionController extends Controller
    {
        public function __construct(private readonly PostingService $posting)
        {
        }

        public function index(Request $request) : AnonymousResourceCollection
        {
            $query = Transaction::with( 'lineItems' )
                                ->orderByDesc( 'transaction_date' )
                                ->orderByDesc( 'id' );

            if ( $request->filled( 'start' ) ) {
                $query->whereDate( 'transaction_date', '>=', $request->input( 'start' ) );
            }
            if ( $request->filled( 'end' ) ) {
                $query->whereDate( 'transaction_date', '<=', $request->input( 'end' ) );
            }
            if ( $request->filled( 'branchId' ) ) {
                $query->where( 'branch_id', $request->input( 'branchId' ) );
            }
            if ( $request->filled( 'transactionType' ) ) {
                $query->where( 'transaction_type', $request->input( 'transactionType' ) );
            }

            $perPage = (int) ( $request->input( 'per_page', 25 ) );

            return TransactionResource::collection( $query->paginate( $perPage ) );
        }

        public function store(TransactionRequest $request) : TransactionResource
        {
            $transaction = $this->posting->post( $request->validated() );

            return new TransactionResource( $transaction->load( 'lineItems' ) );
        }

        public function show(int $id) : TransactionResource
        {
            return new TransactionResource( Transaction::with( 'lineItems' )->findOrFail( $id ) );
        }
    }
