<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * Per-account targets for a fiscal period (IFRS reporting period). Lines hold
     * {accountId, amount} and round-trip 1:1 with the frontend Budget type.
     *
     * @property int    $id
     * @property int    $entity_id
     * @property string $name
     * @property int    $period_id
     * @property array  $lines
     */
    class Budget extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_budgets';

        protected $fillable = [
            'entity_id',
            'name',
            'period_id',
            'lines',
        ];

        protected $casts = [
            'lines' => 'array',
        ];
    }
