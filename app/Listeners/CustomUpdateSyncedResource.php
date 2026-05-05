<?php

    namespace App\Listeners;

    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Stancl\Tenancy\Events\SyncedResourceChangedInForeignDatabase;
    use Stancl\Tenancy\Listeners\UpdateSyncedResource as BaseUpdateSyncedResource;

    class CustomUpdateSyncedResource extends BaseUpdateSyncedResource
    {
        protected function updateResourceInCentralDatabaseAndGetTenants($event, $syncedAttributes)
        {
            $centralModelClass = $event->model->getCentralModelName();
            $globalKey         = $event->model->getGlobalIdentifierKeyName();
            $globalValue       = $event->model->getGlobalIdentifierKey();

            $centralModel = null;

            $centralModelClass::withoutEvents(function () use (&$centralModel, $syncedAttributes, $event, $centralModelClass, $globalKey, $globalValue) {
                $attributes = collect($event->model->getAttributes())->except(['id'])->toArray();

                // 1. Primary Lookup: Global ID
                if (!empty($globalValue)) {
                    $centralModel = $centralModelClass::where($globalKey, $globalValue)->first();
                }

                // 2. Fallback Lookups
                if (!$centralModel && !empty($attributes['username'])) {
                    $centralModel = $centralModelClass::where('username', $attributes['username'])->first();
                }
                if (!$centralModel && !empty($attributes['email'])) {
                    $centralModel = $centralModelClass::where('email', $attributes['email'])->first();
                }
                if (!$centralModel && !empty($attributes['phone'])) {
                    $centralModel = $centralModelClass::where('phone', $attributes['phone'])->first();
                }

                if ($centralModel) {
                    // 3. COLLISION PREVENTION
                    // Strip unique attributes if they belong to a DIFFERENT central user.
                    $uniqueFields = ['email', 'username', 'phone'];

                    foreach ($uniqueFields as $field) {
                        if (!empty($attributes[$field])) {
                            $conflict = $centralModelClass::where($field, $attributes[$field])
                                                          ->where('id', '!=', $centralModel->id)
                                                          ->exists();
                            if ($conflict) {
                                unset($attributes[$field]); // Drop the conflicting field to prevent SQL crash
                            }
                        }
                    }

                    // 4. Update the safe attributes
                    $centralModel->fill($attributes);

                    // Keep global_id aligned
                    if ($centralModel->$globalKey !== $globalValue && !empty($globalValue)) {
                        $centralModel->$globalKey = $globalValue;
                    }

                    $centralModel->save();
                } else {
                    // If it's a completely new user without conflicts, create them
                    $centralModel = $centralModelClass::create($attributes);
                }

                event(new SyncedResourceChangedInForeignDatabase($event->model, null));
            });

            $centralModel->load('tenants');

            $currentTenantMapping = fn($model) => (string) $model->pivot->tenant_id === (string) $event->tenant->getTenantKey();

            $mappingExists = $centralModel->tenants->contains($currentTenantMapping);

            if (!$mappingExists) {
                Pivot::withoutEvents(function () use ($centralModel, $event) {
                    $centralModel->tenants()->attach($event->tenant->getTenantKey());
                });
            }

            return $centralModel->tenants->filter(fn($model) => !$currentTenantMapping($model));
        }
    }