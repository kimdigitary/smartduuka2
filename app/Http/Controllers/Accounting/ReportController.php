<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Services\Accounting\ReportService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;

    class ReportController extends Controller
    {
        public function __construct(private readonly ReportService $reports)
        {
        }

        public function trialBalance(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->trialBalance( $request->input( 'end' ) ) );
        }

        public function incomeStatement(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->incomeStatement( $request->input( 'start' ), $request->input( 'end' ) ) );
        }

        public function balanceSheet(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->balanceSheet( $request->input( 'end' ) ) );
        }

        public function cashFlow(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->cashFlow( $request->input( 'start' ), $request->input( 'end' ) ) );
        }

        public function accountStatement(Request $request) : JsonResponse
        {
            $request->validate( [ 'accountId' => [ 'required', 'integer' ] ] );

            return $this->ok( $this->reports->accountStatement(
                (int) $request->input( 'accountId' ),
                $request->input( 'start' ),
                $request->input( 'end' ),
            ) );
        }

        public function vatReturn(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->vatReturn( $request->input( 'start' ), $request->input( 'end' ) ) );
        }

        public function aging(Request $request) : JsonResponse
        {
            return $this->ok( $this->reports->aging(
                $request->input( 'type', 'RECEIVABLE' ),
                $request->input( 'asOf' ),
            ) );
        }

        private function ok(array $data) : JsonResponse
        {
            return response()->json( [ 'data' => $data ] );
        }
    }
