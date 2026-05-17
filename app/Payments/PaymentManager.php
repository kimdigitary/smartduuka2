<?php

    namespace App\Payments;

    use App\Payments\Contracts\PaymentGateway;
    use App\Payments\Gateways\IotecGateway;
    use App\Payments\Gateways\JPesaGateway;
    use App\Payments\Gateways\YoUgandaGateway;
    use InvalidArgumentException;

    class PaymentManager
    {
        /** @var array<string, class-string<PaymentGateway>> */
        private array $gateways = [
            'iotec'     => IotecGateway::class ,
            'yo_uganda' => YoUgandaGateway::class ,
            'jpesa'     => JPesaGateway::class ,
        ];

        /**
         * Resolve a gateway by its key.
         *
         * @throws InvalidArgumentException
         */
        public function gateway(string $name) : PaymentGateway
        {
            if ( ! isset( $this->gateways[ $name ] ) ) {
                throw new InvalidArgumentException( "Payment gateway [{$name}] is not supported." );
            }

            return app( $this->gateways[ $name ] );
        }

        /**
         * Resolve the application's default gateway (set in config/payments.php).
         */
        public function default() : PaymentGateway
        {
            return $this->gateway( config( 'payments.default' , 'yo_uganda' ) );
        }

        /**
         * Register a custom gateway at runtime.
         *
         * @param class-string<PaymentGateway> $class
         */
        public function extend(string $name , string $class) : void
        {
            $this->gateways[ $name ] = $class;
        }
    }
