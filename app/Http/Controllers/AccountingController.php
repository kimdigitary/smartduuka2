<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEntityRequest;
use IFRS\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    public function getEntity(): JsonResponse
    {
        $entity = Auth::user()->entity;

        if (!$entity) {
            return response()->json(null);
        }

        return response()->json([
            'name'          => $entity->name,
            'currencyId'    => $entity->currency_id,
            'yearStart'     => $entity->year_start,
            'multiCurrency' => $entity->multi_currency,
            'locale'        => $entity->locale,
        ]);
    }

    public function sveEntity(SaveEntityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = Auth::user();

        $entityData = [
            'name'           => $data['name'],
            'currency_id'    => $data['currencyId'],
            'year_start'     => $data['yearStart'],
            'multi_currency' => $data['multiCurrency'],
        ];

        if ($user->entity) {
            $user->entity->update($entityData);
        } else {
            $entity = Entity::create($entityData);
            $user->entity_id = $entity->id;
            $user->save();
        }

        return response()->json();
    }
}
