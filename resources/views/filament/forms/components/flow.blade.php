<div class="flex flex-row justify-between w-full items-center">
    @foreach($getRecord()->pipeline->stages as $stage)
            @if ($stage->state === 'step' || $stage->state === 'action')
                <div class="flex flex-col rounded-full items-center text-center space-y-6">
                        @if ($getRecord()->stage->order > $stage->order)
                            <div class="rounded-full p-4 shadow-sm" style="background-color: rgba(var(--success-50), 1); border-color: rgba(var(--success-400), 0.1); border-width: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6" style="stroke: rgba(var(--success-600), 1)" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-normal" style="color: rgba(var(--success-600), 1)">{{ $stage->name }}</p>
                        @elseif ($getRecord()->stage->order === $stage->order)
                            <div class="rounded-full p-4 shadow-sm" style="background-color: rgba(var(--info-50), 1); border-color: rgba(var(--info-400), 0.1); border-width: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6" style="stroke: rgba(var(--info-600), 1)" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium" style="color: rgba(var(--info-600), 1)">{{ $stage->name }}</p>
                        @else
                            <div class="rounded-full p-4 shadow-sm" style="background-color: rgba(var(--gray-50), 1); border-color: rgba(var(--gray-400), 0.1); border-width: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6" style="stroke: rgba(var(--gray-600), 1)" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-normal" style="color: rgba(var(--gray-600), 1)">{{ $stage->name }}</p>
                    @endif
                    </div>
                    @if ($getRecord()->stage->order - 1 >= $stage->order)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-4 h-4 -mt-6" style="stroke: rgba(var(--success-600), 1)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-4 h-4 -mt-6" style="stroke: rgba(var(--gray-600), 1)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    @endif
            @elseif($stage->state === 'completed')
                <div class="flex flex-col items-center text-center space-y-6">
                    @if($getRecord()->stage->order >= $stage->order)
                        <div class="rounded-full p-4 shadow-sm" style="background-color: rgba(var(--success-50), 1); border-color: rgba(var(--success-400), 0.1); border-width: 2px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6" fill="none" style="stroke: rgba(var(--success-600), 1)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-normal" style="color: rgba(var(--success-600), 1)">{{ $stage->name }}</p>
                    @else
                        <div class="rounded-full p-4 shadow-sm" style="background-color: rgba(var(--gray-50), 1); border-color: rgba(var(--gray-400), 0.1); border-width: 2px;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6" fill="none" style="stroke: rgba(var(--gray-600), 1)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-normal" style="color: rgba(var(--gray-600), 1)">{{ $stage->name }}</p>
                    @endif
                </div>
            @endif
    @endforeach
</div>
