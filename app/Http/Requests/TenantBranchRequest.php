<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantBranchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'share_customers'   => filter_var($this->share_customers, FILTER_VALIDATE_BOOLEAN),
            'share_wallets'     => filter_var($this->share_wallets, FILTER_VALIDATE_BOOLEAN),
            'share_loyalty'     => filter_var($this->share_loyalty, FILTER_VALIDATE_BOOLEAN),
            'share_accounting'  => filter_var($this->share_accounting, FILTER_VALIDATE_BOOLEAN),
            'share_reports'     => filter_var($this->share_reports, FILTER_VALIDATE_BOOLEAN),
            'share_procurement' => filter_var($this->share_procurement, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        $tenantBranch = $this->route('tenant_branch')
            ?? $this->route('tenantBranch')
            ?? $this->route('branch');

        return [
            'name'              => [
                'required',
                Rule::unique('tenant_branches', 'name')
                    ->where('tenant_id', $this->input('tenant_id'))
                    ->ignore($tenantBranch),
            ],
            'email'             => [
                'required',
                'email',
                'max:254',
                Rule::unique('tenant_branches', 'email')
                    ->where('tenant_id', $this->input('tenant_id'))
                    ->ignore($tenantBranch),
            ],
            'tenant_id'         => ['required', 'exists:tenants,id'],
            'website'           => [
                'required',
                Rule::unique('tenant_branches', 'website')
                    ->where('tenant_id', $this->input('tenant_id'))
                    ->ignore($tenantBranch),
            ],
            'zip_code'          => ['required'],
            'country'           => ['required', 'integer'],
            //                'city'      => [ 'required' , 'integer' ] ,
            'state'             => ['required', 'integer'],
            'address'           => ['required'],
            'phone'             => [
                'required',
                Rule::unique('tenant_branches', 'phone')
                    ->where('tenant_id', $this->input('tenant_id'))
                    ->ignore($tenantBranch),
            ],
            'phone2'            => [
                'required',
                Rule::unique('tenant_branches', 'phone2')
                    ->where('tenant_id', $this->input('tenant_id'))
                    ->ignore($tenantBranch),
            ],
            //                'code'      => [ 'required' ] ,
            'status'            => ['required', 'integer'],
            'share_customers'   => ['required', 'boolean'],
            'share_wallets'     => ['required', 'boolean'],
            'share_loyalty'     => ['required', 'boolean'],
            'share_accounting'  => ['required', 'boolean'],
            'share_reports'     => ['required', 'boolean'],
            'share_procurement' => ['required', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
