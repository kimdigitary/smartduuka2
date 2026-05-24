<?php

    namespace App\Logging;

    use Illuminate\Log\Logger;
    use Monolog\Formatter\LineFormatter;
    use Monolog\Level;
    use Monolog\Processor\IntrospectionProcessor;

    class FormatLogLocation
    {
        /**
         * Customize the given logger instance.
         */
        public function __invoke(Logger $logger) : void
        {
            // 1. Set up the Introspection Processor
            $introspectionProcessor = new IntrospectionProcessor( Level::Debug , [ 'Illuminate\\' ] );

            // 2. Define the log structure
            $format = "[%datetime%] %channel%.%level_name%: [%extra.file%:%extra.line%] %message% %context%\n";

            $formatter = new LineFormatter( $format , NULL , TRUE , TRUE );
            $formatter->addJsonEncodeOption( JSON_PRETTY_PRINT );
            $formatter->addJsonEncodeOption( JSON_UNESCAPED_SLASHES );

            // 3. Apply everything to the handlers
            foreach ( $logger->getHandlers() as $handler ) {

                // Pushed FIRST, so it executes LAST (after the file path is generated)
                $handler->pushProcessor( function ($record) {
                    $extra = $record->extra;

                    if ( isset( $extra[ 'file' ] ) ) {
                        // Strip the absolute base path off the string, leaving a relative path
                        $basePath        = base_path() . DIRECTORY_SEPARATOR;
                        $extra[ 'file' ] = str_replace( $basePath , '' , $extra[ 'file' ] );
                    }

                    // Return the updated LogRecord (Required for modern Monolog versions)
                    return $record->with( extra: $extra );
                } );

                // Pushed SECOND, so it executes FIRST (generating the file and line data)
                $handler->pushProcessor( $introspectionProcessor );

                $handler->setFormatter( $formatter );
            }
        }
    }