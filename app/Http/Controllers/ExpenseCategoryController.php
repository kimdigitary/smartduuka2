<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Http\Resources\ProductCategoryDepthTreeResource;
use App\Models\ExpenseCategory;
use App\Traits\ApiResponse;
use App\Traits\AuthUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseCategoryController extends Controller
{
    use ApiResponse, AuthUser;

    public function index(Request $request)
    {
        $name = $request->name;
        $branch_id = $request->branch_id;
        $data = ExpenseCategory::branch($branch_id)->tree()->depthFirst()->when($name, function ($query) use ($name) {
            $query->where('name', 'like', "%$name%");
        })->with('parent_category', 'expenses')->get();
        return ExpenseCategoryResource::collection($data);
    }

    public function depthTree()
    {
        try {
            return ProductCategoryDepthTreeResource::collection(ExpenseCategory::tree()->depthFirst()->with('parent_category')->get());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(ExpenseCategoryRequest $request)
    {
        $expense_category = ExpenseCategory::create($request->validated());
        return $this->response(success: true, message: 'success', data: $expense_category);
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update($request->validated());
        return $this->response(success: true, message: 'success', data: $expenseCategory->fresh());
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $ids = $request->input('ids');

        if (ExpenseCategory::whereIn('id', $ids)->whereHas('children')->exists()) {
            return $this->error(message: 'Cannot delete category with sub-categories.');
        }

        if (ExpenseCategory::whereIn('id', $ids)->whereHas('expenses')->exists()) {
            return $this->error(message: 'Cannot delete category with associated expenses.');
        }

        ExpenseCategory::destroy($ids);

        return $this->response(success: true, message: 'success');
    }
}
