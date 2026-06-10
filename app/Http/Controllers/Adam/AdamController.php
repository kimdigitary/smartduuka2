<?php

    namespace App\Http\Controllers\Adam;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;

    class AdamController extends Controller
    {
        /**
         * The target remote API base URL.
         * Trailing slash is included to prevent the remote server from triggering
         * 301 redirects that accidentally strip out POST payloads.
         */
        private string $url = 'https://www.qualitywifi.xyz/api/';

        /**
         * Helper method to return only the raw body and status.
         * This ensures headers like 'Location' from the remote server are dropped,
         * maintaining clean communication with the local frontend.
         */
        private function proxyResponse($response)
        {
            return response( $response->body() , $response->status() )
                ->header( 'Content-Type' , 'application/json' );
        }

        public function pay(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->post( $this->url . 'pay' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        public function success(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->post( $this->url . 'success' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        public function index(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->get( $this->url . 'vouchers' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        public function checkUserAdded(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->get( $this->url . 'checkuseradded' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        public function routers(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->get( $this->url . 'routers' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        /**
         * Fetch individual router packages.
         */
        public function packages(string $router)
        {
            try {
                $response = Http::acceptJson()
                                ->get( $this->url . 'packages/' . $router . '/' );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        public function pay2(Request $request)
        {
            try {
                $response = Http::acceptJson()
                                ->post( $this->url . 'pay' , $request->all() );
                return $this->proxyResponse( $response );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }
    }