<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\AccountRequest;
    use App\Http\Resources\Accounting\AccountResource;
    use App\Services\Accounting\ReportService;
    use IFRS\Models\Account;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Auth;

    class AccountController extends Controller
    {
        public function __construct(private readonly ReportService $reports)
        {
        }

        public function index() : AnonymousResourceCollection
        {
            $balances = $this->reports->accountBalances();
            $accounts = Account::orderBy( 'code' )->get();
            $accounts->each( fn (Account $a) => $a->setAttribute( 'report_balance', $balances[ $a->id ] ?? 0 ) );

            return AccountResource::collection( $accounts );
        }

        public function store(AccountRequest $request) : AccountResource
        {
            return new AccountResource( $this->fill( new Account(), $request->validated() ) );
        }

        public function update(AccountRequest $request, int $id) : AccountResource
        {
            return new AccountResource( $this->fill( Account::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Account::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Account $account, array $data) : Account
        {
            $account->name         = $data[ 'name' ];
            $account->account_type = $data[ 'accountType' ];
            if ( ! empty( $data[ 'code' ] ) ) {
                $account->code = $data[ 'code' ];
            }
            $account->category_id   = $data[ 'categoryId' ] ?? NULL;
            $account->currency_id   = $data[ 'currencyId' ] ?? Auth::user()?->entity?->currency_id;
            $account->description   = $data[ 'description' ] ?? NULL;
            $account->is_active     = $data[ 'isActive' ] ?? TRUE;
            $account->is_petty_cash = $data[ 'isPettyCash' ] ?? FALSE;
            $account->party_type    = $data[ 'partyType' ] ?? NULL;
            $account->party_id      = $data[ 'partyExternalId' ] ?? NULL;
            $account->save();

            return $account;
        }
    }
