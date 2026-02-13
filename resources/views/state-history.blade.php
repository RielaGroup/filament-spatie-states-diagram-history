@php
    $records = $records ?? collect();
@endphp
<div class="w-full mx-auto">
    @if($records->isNotEmpty())
        <div class="space-y-8">
            @foreach($records as $index => $record)
                <div class="relative flex gap-x-6">
                    @if(!$loop->last)
                        <div class="absolute left-[20px] top-9 w-[2px] h-full bg-blue-100 dark:bg-blue-900/30"></div>
                    @endif

                    <div class="relative z-10">
                        @if($record->comment)
                            <div class="h-10 w-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex items-baseline gap-x-3 flex-wrap">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->user?->name ?? __('System') }}</p>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('changed the state to') }}</span>
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $record->getStateToLabel() }}</span>
                        </div>

                        <time class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $record->created_at->format('M jS - Y @ H:i:s') }}
                        </time>

                        @if($record->comment)
                            <div class="mt-3 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $record->comment }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="mt-2 text-sm">{{ __('No state history recorded') }}</p>
        </div>
    @endif
</div>
