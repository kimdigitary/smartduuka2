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
            // 1. Set up the Introspection Processor to get file and line numbers.
            // We pass 'Illuminate\\' to skip Laravel's core files in the stack trace.
            $processor = new IntrospectionProcessor( Level::Debug , [ 'Illuminate\\' ] );

            // 2. Define how the log line is structured.
            $format = "[%datetime%] %channel%.%level_name%: [%extra.file%:%extra.line%] %message% %context%\n";

            // 3. Initialize the LineFormatter.
            // The 3rd argument (true) allows inline line breaks so JSON doesn't print on one massive line.
            // The 4th argument (true) ignores empty context arrays instead of printing [].
            $formatter = new LineFormatter( $format , NULL , TRUE , TRUE );

            // 4. Enforce highly readable JSON/Array formatting.
            $formatter->addJsonEncodeOption( JSON_PRETTY_PRINT );
            $formatter->addJsonEncodeOption( JSON_UNESCAPED_SLASHES );

            // 5. Apply the processor and formatter to all handlers for this channel.
            foreach ( $logger->getHandlers() as $handler ) {
                $handler->pushProcessor( $processor );
                $handler->setFormatter( $formatter );
            }
        }
    }