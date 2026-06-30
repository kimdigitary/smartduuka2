<?php

    namespace App\Exceptions\Accounting;

    use RuntimeException;

    /**
     * Thrown when a posting targets a CLOSED reporting period. Caught by the
     * operational posting path so the failure is recorded as a posting alert
     * (rather than silently swallowed) and surfaced to the user for a manual
     * adjusting entry.
     */
    class ClosedPeriodException extends RuntimeException
    {
    }
