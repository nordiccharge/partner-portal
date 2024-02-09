@php
    use \Illuminate\Support\Js;
@endphp
@if ($this->record instanceof \App\Models\Inventory)
    <x-filament-panels::page>
        <div class="space-y-6">
            @foreach($this->paginateTableQuery(
            $this->record->activities()->where('description', '!=', 'updated')->where('description', 'not like', 'activity.logs%')->with('causer')->latest()->getQuery()) as $activityItem)
                <div @class([
                'p-2 space-y-2 bg-white rounded-xl shadow',
                'dark:border-gray-600 dark:bg-gray-800',
            ])>
                    <div class="p-2">
                        <div class="flex justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col text-left">

                                    <span class="font-medium">{{ $activityItem->description }}</span>
                                    <span class="text-xs">{{ $activityItem->causer?->name }}</span>
                                    <span class="text-xs text-gray-500">
                                    {{ $activityItem->created_at->format(__('filament-activity-log::activities.default_datetime_format')) }}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <x-filament::pagination
                :page-options="$this->getTableRecordsPerPageSelectOptions()"
                :paginator="$this->paginateTableQuery(
            $this->record->activities()->where('description', '!=', 'updated')->where('description', 'not like', 'activity.logs%')->with('causer')->latest()->getQuery())"
                class="px-3 py-3 sm:px-6"
            />
        </div>
    </x-filament-panels::page>
@else
    <x-filament-panels::page>
        <div class="space-y-6">
            @foreach($this->getActivities() as $activityItem)
                <div @class([
                    'p-2 space-y-2 bg-white rounded-xl shadow',
                    'dark:border-gray-600 dark:bg-gray-800',
                ])>
                    <div class="p-2">
                        <div class="flex justify-between">
                            <div class="flex items-center gap-4">
                                @if ($activityItem->causer && $activityItem->event != 'system')
                                    <x-filament-panels::avatar.user :user="$activityItem->causer" class="!w-7 !h-7"/>
                                @endif
                                <div class="flex flex-col text-left">
                                    <span class="font-bold">
                                        @if($activityItem->event != 'system')
                                            {{ $activityItem->causer?->name }}
                                        @else
                                            System Automation
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500 pb-4">
                                        @if($activityItem->event != 'system')
                                            @lang('filament-activity-log::activities.events.' . $activityItem->event)
                                        @endif
                                        {{ $activityItem->created_at->format(__('filament-activity-log::activities.default_datetime_format')) }}
                                    </span>
                                    <span class="text-sm">
                                        {{ $activityItem->description }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($activityItem->event != 'system')
                        <x-filament-tables::table class="w-full overflow-hidden text-sm">
                        <x-slot:header>
                            <x-filament-tables::header-cell>
                                @lang('filament-activity-log::activities.table.field')
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>
                                @lang('filament-activity-log::activities.table.old')
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell>
                                @lang('filament-activity-log::activities.table.new')
                            </x-filament-tables::header-cell>
                        </x-slot:header>
                        @php
                            /* @var \Spatie\Activitylog\Models\Activity $activityItem */
                            $changes = $activityItem->getChangesAttribute();
                        @endphp
                        @foreach(data_get($changes, 'attributes', []) as $field => $change)
                            @php
                                $oldValue = data_get($changes, "old.{$field}");
                                $newValue = data_get($changes, "attributes.{$field}");
                            @endphp
                            <x-filament-tables::row @class(['bg-gray-100/30' => $loop->even])>
                                <x-filament-tables::cell width="20%" class="px-4 py-2 align-top sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    {{ $this->getFieldLabel($field) }}
                                </x-filament-tables::cell>
                                <x-filament-tables::cell width="40%" class="px-4 py-2 align-top break-all !whitespace-normal">
                                    @if(is_array($oldValue))
                                        <pre class="text-xs text-gray-500">{{ json_encode($oldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        {{ $oldValue }}
                                    @endif
                                </x-filament-tables::cell>
                                <x-filament-tables::cell width="40%" class="px-4 py-2 align-top break-all !whitespace-normal">
                                    @if(is_array($newValue))
                                        <pre class="text-xs text-gray-500">{{ json_encode($newValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        {{ $newValue }}
                                    @endif
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endforeach
                    </x-filament-tables::table>
                    @endif
                </div>
            @endforeach

            <x-filament::pagination
                :page-options="$this->getTableRecordsPerPageSelectOptions()"
                :paginator="$this->getActivities()"
                class="px-3 py-3 sm:px-6"
            />
        </div>
    </x-filament-panels::page>
@endif
