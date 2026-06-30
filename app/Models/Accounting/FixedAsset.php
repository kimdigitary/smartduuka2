<?php

    namespace App\Models\Accounting;

    use App\Models\Accounting\Concerns\ScopedToEntity;
    use Illuminate\Database\Eloquent\Model;

    /**
     * A depreciable fixed asset. Depreciation and disposal post IFRS journals from
     * the frontend; this record tracks cost, accumulated depreciation and status.
     *
     * @property int         $id
     * @property int         $entity_id
     * @property string      $name
     * @property string|null $code
     * @property int         $asset_account_id
     * @property float       $cost
     * @property float       $salvage_value
     * @property string      $acquisition_date
     * @property int         $useful_life_years
     * @property string      $method
     * @property float       $accumulated_depreciation
     * @property string      $status
     * @property string|null $branch_id
     * @property string|null $disposal_date
     * @property float|null  $disposal_proceeds
     */
    class FixedAsset extends Model
    {
        use ScopedToEntity;

        protected $table = 'accounting_fixed_assets';

        protected $fillable = [
            'entity_id',
            'name',
            'code',
            'asset_account_id',
            'cost',
            'salvage_value',
            'acquisition_date',
            'useful_life_years',
            'method',
            'accumulated_depreciation',
            'status',
            'branch_id',
            'disposal_date',
            'disposal_proceeds',
        ];

        protected $casts = [
            'cost'                     => 'decimal:4',
            'salvage_value'            => 'decimal:4',
            'accumulated_depreciation' => 'decimal:4',
            'disposal_proceeds'        => 'decimal:4',
            'useful_life_years'        => 'integer',
            'acquisition_date'         => 'date',
            'disposal_date'            => 'date',
        ];
    }
