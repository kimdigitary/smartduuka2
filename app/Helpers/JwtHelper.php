<?php

    namespace App\Helpers;

    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\SignatureInvalidException;

    class JwtHelper
    {
        private static string $algo             = 'HS256';
        private static int    $expiresInMinutes = 60 * 24 * 30; // 30 days

        private static function secret() : string
        {
            return config( 'jwt.secret' );
        }

        /**
         * Sign a payload and return a JWT token string.
         */
        public static function sign(array $claims) : string
        {
            $claims[ 'iat' ] = now()->timestamp;
            $claims[ 'exp' ] = now()->addMinutes( self::$expiresInMinutes )->timestamp;

            return JWT::encode( $claims , self::secret() , self::$algo );
        }

        /**
         * Decode and verify a token using the package.
         * Returns null if invalid or malformed.
         */
        public static function decode(string $token) : ?array
        {
            try {
                return (array) JWT::decode( $token , new Key( self::secret() , self::$algo ) );
            } catch ( \Exception ) {
                return NULL;
            }
        }

        /**
         * Verify a token and auto-refresh with a fresh 30-day expiry.
         * As long as the user is active the token never expires in practice.
         *
         * Returns:
         *   ['status' => 'ok',      'payload' => [...], 'newToken' => 'fresh.jwt.token']
         *   ['status' => 'invalid', 'payload' => null,  'newToken' => null]
         *   ['status' => 'error',   'payload' => null,  'newToken' => null]
         */
        public static function verify(string $token) : array
        {
            try {
                $payload  = (array) JWT::decode( $token , new Key( self::secret() , self::$algo ) );
                $newToken = self::sign( self::stripReservedClaims( $payload ) );

                return [
                    'status'   => 'ok' ,
                    'payload'  => $payload ,
                    'newToken' => $newToken ,
                ];

            } catch ( ExpiredException $e ) {
                // Token expired but we still extract the payload and re-sign
                $payload  = (array) $e->getPayload();
                $newToken = self::sign( self::stripReservedClaims( $payload ) );

                return [
                    'status'   => 'ok' ,
                    'payload'  => $payload ,
                    'newToken' => $newToken ,
                ];

            } catch ( SignatureInvalidException ) {
                return [ 'status' => 'invalid' , 'payload' => NULL , 'newToken' => NULL ];

            } catch ( \Exception ) {
                return [ 'status' => 'error' , 'payload' => NULL , 'newToken' => NULL ];
            }
        }

        /**
         * Remove JWT reserved claims before re-signing.
         */
        private static function stripReservedClaims(array $payload) : array
        {
            return array_diff_key( $payload , array_flip( [ 'iat' , 'exp' , 'nbf' , 'jti' , 'iss' , 'aud' , 'sub' ] ) );
        }
    }