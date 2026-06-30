<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * A repeating balanced journal template. The frontend "runs" it to post an
     * IFRS transaction and then rolls next_run_date forward by `frequency`.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property string      $name
     * @property string      $frequency
     * @property string      $start_date
     * @property string      $next_run_date
     * @property string|null $end_date
     * @property string|null $narration
     * @property string|null $reference
     * @property string|null $branch_id
     * @property array       $lines
     * @property bool        $active
     * @property string|null $last_run_date
     */
    class RecurringJournal extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_recurring_journals';

        protected $fillable = [
            'entity_id',
            'name',
            'frequency',
            'start_date',
            'next_run_date',
            'end_date',
            'narration',
            'reference',
            'branch_id',
            'lines',
            'active',
            'last_run_date',
        ];

        protected $casts = [
            'lines'         => 'array',
            'active'        => 'boolean',
            'start_date'    => 'date',
            'next_run_date' => 'date',
            'end_date'      => 'date',
            'last_run_date' => 'date',
        ];
    }
