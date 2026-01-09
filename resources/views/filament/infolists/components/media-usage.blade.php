@php
    $record = $getRecord();
    $references = $record->references()->with('model')->get();
    
    // Sammle alle Verwendungen: Direkte Bindung + Referenzen
    $allUsages = collect();
    
    // 1. Direkte Spatie Media Library Bindung (model_type/model_id)
    if ($record->model_type && $record->model_id) {
        try {
            $boundModel = app($record->model_type)->find($record->model_id);
            if ($boundModel) {
                // Bei SeoMetadata: Eager load das Parent-Model
                if ($record->model_type === 'App\\Models\\SeoMetadata' && method_exists($boundModel, 'model')) {
                    $boundModel->load('model');
                }
                
                $allUsages->push([
                    'type' => 'direct',
                    'model_type' => $record->model_type,
                    'model' => $boundModel,
                    'collection' => $record->collection_name ?? 'default',
                ]);
            }
        } catch (\Exception $e) {
            // Model existiert nicht mehr
        }
    }
    
    // 2. MediaReference Einträge
    foreach ($references as $ref) {
        if ($ref->model) {
            $allUsages->push([
                'type' => 'reference',
                'model_type' => $ref->model_type,
                'model' => $ref->model,
                'collection' => $ref->collection_name,
            ]);
        }
    }
    
    $totalUsages = $allUsages->count();
@endphp

<div style="border-radius: 0.5rem; border-width: 1px; padding: 1rem; @if($totalUsages === 0) border-color: #22c55e; background-color: #f0fdf4; @else border-color: #f59e0b; background-color: #fffbeb; @endif">
    @if($totalUsages === 0)
        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <svg style="height: 20px; width: 20px; min-height: 20px; min-width: 20px; max-height: 20px; max-width: 20px; color: #16a34a; margin-top: 2px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p style="font-weight: 600; color: #15803d; margin-bottom: 0.25rem;">Keine Verwendung</p>
                <p style="font-size: 0.875rem; color: #166534;">Dieses Medium wird nirgendwo verwendet und kann sicher gelöscht werden.</p>
            </div>
        </div>
    @else
        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <svg style="height: 20px; width: 20px; min-height: 20px; min-width: 20px; max-height: 20px; max-width: 20px; color: #d97706; margin-top: 2px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div style="flex: 1;">
                <p style="font-weight: 600; color: #92400e; margin-bottom: 0.25rem;">
                    {{ $totalUsages }} {{ $totalUsages === 1 ? 'Verwendung' : 'Verwendungen' }} gefunden
                </p>
                <p style="font-size: 0.875rem; color: #b45309; margin-bottom: 0.75rem;">
                    Dieses Medium kann nicht gelöscht werden, solange diese Verwendungen bestehen.
                </p>

                @php
                    $groupedUsages = $allUsages->groupBy('model_type');
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($groupedUsages as $modelType => $usages)
                        <div>
                            <h4 style="font-size: 0.875rem; font-weight: 600; color: #92400e; margin-bottom: 0.25rem;">
                                {{ class_basename($modelType) }}
                            </h4>
                            <ul style="list-style: none; padding-left: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                @foreach($usages as $usage)
                                    @php
                                        $modelInstance = $usage['model'];
                                        $identifier = $modelInstance?->title ?? $modelInstance?->name ?? "ID: {$modelInstance->id}";
                                        $collection = $usage['collection'];
                                        $usageType = $usage['type'] === 'direct' ? 'Direkt gebunden' : 'Referenziert';
                                        
                                        // Bei SeoMetadata: Finde das Parent-Model
                                        $linkModel = $modelInstance;
                                        $linkIdentifier = null;
                                        
                                        if ($modelInstance && class_basename(get_class($modelInstance)) === 'SeoMetadata') {
                                            // SeoMetadata hat ein morphTo-Relationship zum Parent
                                            if ($modelInstance->model) {
                                                $linkModel = $modelInstance->model;
                                                $linkIdentifier = $linkModel->slug ?? $linkModel->id;
                                                $identifier = ($linkModel->title ?? $linkModel->name ?? "ID: {$linkModel->id}") . " (SEO)";
                                            }
                                        } else {
                                            $linkIdentifier = $modelInstance->slug ?? $modelInstance->id;
                                        }
                                        
                                        // Mapping für bekannte Models zu Filament-Routen
                                        $resourceMap = [
                                            'Post' => 'filament.admin.resources.posts.edit',
                                            'Category' => 'filament.admin.resources.categories.edit',
                                            'Tag' => 'filament.admin.resources.tag-resource.tags.edit',
                                        ];
                                        
                                        $editUrl = null;
                                        $modelBasename = class_basename(get_class($linkModel));
                                        
                                        if (isset($resourceMap[$modelBasename]) && $resourceMap[$modelBasename] && $linkIdentifier) {
                                            try {
                                                if (\Illuminate\Support\Facades\Route::has($resourceMap[$modelBasename])) {
                                                    $editUrl = route($resourceMap[$modelBasename], ['record' => $linkIdentifier]);
                                                }
                                            } catch (\Exception $e) {
                                                // Route existiert nicht
                                            }
                                        }
                                    @endphp
                                    <li style="font-size: 0.875rem; color: #b45309;">
                                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                                            <svg style="height: 12px; width: 12px; min-height: 12px; min-width: 12px; max-height: 12px; max-width: 12px; flex-shrink: 0; margin-top: 4px;" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span style="flex: 1;">
                                                @if($editUrl)
                                                    <a href="{{ $editUrl }}" style="font-weight: 500; color: #d97706; text-decoration: underline; text-decoration-style: dotted; text-underline-offset: 2px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                        {{ $identifier }}
                                                        <svg style="height: 12px; width: 12px; min-height: 12px; min-width: 12px; max-height: 12px; max-width: 12px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                    </a>
                                                @else
                                                    {{ $identifier }}
                                                @endif
                                                <span style="font-size: 0.75rem; opacity: 0.75; display: block; margin-top: 0.125rem;">
                                                    {{ $usageType }}: {{ $collection }}
                                                </span>
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
