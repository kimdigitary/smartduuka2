<?php

    namespace App\Http\Resources\Accounting;

    use Carbon\Carbon;
    use IFRS\Models\ReportingPeriod;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Facades\Auth;

    /** @mixin ReportingPeriod */
    class PeriodResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $yearStart = (int) ( Auth::user()?->entity?->year_start ?: 1 );
            $start     = Carbon::create( $this->calendar_year, $yearStart, 1 )->startOfDay();
            $end       = $start->copy()->addYear()->subDay();

            // Calendar fiscal year (Jan start) -> "2025"; offset fiscal year -> "2025/2026".
            $label = $yearStart === 1
                ? (string) $this->calendar_year
                : $this->calendar_year . '/' . ( $this->calendar_year + 1 );

            return [
                'id'           => $this->id,
                'label'        => $label,
                'calendarYear' => $this->calendar_year,
                'periodCount'  => $this->period_count,
                'status'       => $this->status,
                'startDate'    => $start->toDateString(),
                'endDate'      => $end->toDateString(),
                'closedAt'     => optional( $this->closed_at )->toDateString(),
                'closedBy'     => $this->closed_by,
            ];
        }
    }
