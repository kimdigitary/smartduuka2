<?php

    namespace App\Logging;

    use Illuminate\Log\Logger;
    use Monolog\Formatter\LineFormatter;

    class FormatLogLocation
    {
        public function __invoke(Logger $logger) : void
        {
            // Define the log structure
            $format = "[%datetime%] %channel%.%level_name%: [%extra.file%:%extra.line%] %message% %context%\n";

            $formatter = new LineFormatter( $format , NULL , TRUE , TRUE );
            $formatter->addJsonEncodeOption( JSON_PRETTY_PRINT );
            $formatter->addJsonEncodeOption( JSON_UNESCAPED_SLASHES );

            foreach ( $logger->getHandlers() as $handler ) {

                // Custom trace processor to completely ignore all vendor files
                $handler->pushProcessor( function ($record) {
                    $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
                    $file  = 'Unknown';
                    $line  = 0;

                    $basePath = base_path() . DIRECTORY_SEPARATOR;

                    foreach ( $trace as $frame ) {
                        if ( isset( $frame[ 'file' ] ) ) {
                            // Skip Laravel framework and Monolog files entirely
                            $isFramework = str_contains( $frame[ 'file' ] , 'vendor' . DIRECTORY_SEPARATOR . 'laravel' );
                            $isMonolog   = str_contains( $frame[ 'file' ] , 'vendor' . DIRECTORY_SEPARATOR . 'monolog' );

                            // The moment we hit a file outside of the vendor/laravel and monolog folders, we grab it.
                            if ( ! $isFramework && ! $isMonolog ) {
                                $file = str_replace( $basePath , '' , $frame[ 'file' ] );
                                $line = $frame[ 'line' ] ?? 0;
                                break;
                            }
                        }
                    }

                    $extra           = $record->extra;
                    $extra[ 'file' ] = $file;
                    $extra[ 'line' ] = $line;

                    return $record->with( extra: $extra );
                } );

                $handler->setFormatter( $formatter );
            }
        }
    }