@php
    $mermaidCode = $diagram['mermaidCode'] ?? '';
    $pathNodeIds = $diagram['pathNodeIds'] ?? [];
    $historyCount = $diagram['historyCount'] ?? 0;
    $historyLoaded = $diagram['historyLoaded'] ?? false;
@endphp
<div class="p-0">
    @if($mermaidCode === '')
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No state diagram available</h3>
            <p class="text-gray-600 dark:text-gray-400">This record does not use a state machine or the state configuration could not be loaded.</p>
        </div>
    @else
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="font-medium text-gray-700 dark:text-gray-300">State history</p>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                @if($historyLoaded)
                    Loaded: <strong>{{ $historyCount }}</strong> transition(s).
                    @if($historyCount > 0)
                        Path: <strong>{{ implode(' → ', $pathNodeIds) }}</strong>
                    @else
                        No transitions recorded for this record.
                    @endif
                @else
                    Not loaded (model has no <code>stateHistory</code> relation).
                @endif
            </p>
        </div>
        @php
            $mermaidUrl = config('filament-spatie-states.mermaid_js_url', 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js');
        @endphp
        <div
            x-data="{
                async init() {
                    await this.$nextTick();
                    const el = this.$refs.diagram;
                    if (!el || el.dataset.rendered === 'true') return;
                    if (!window.mermaid) {
                        await new Promise((resolve) => {
                            const s = document.createElement('script');
                            s.src = @js($mermaidUrl);
                            s.onload = resolve;
                            document.head.appendChild(s);
                        });
                    }
                    window.mermaid.initialize({ startOnLoad: false, theme: 'base' });
                    try {
                        await window.mermaid.run({ nodes: [el] });
                        el.dataset.rendered = 'true';
                    } catch (err) {
                        console.warn('Mermaid render failed:', err);
                    }
                }
            }"
            x-init="init()"
        >
            <div
                class="mermaid state-diagram-mermaid p-4 min-h-[300px] text-center"
                x-ref="diagram"
            >{{ $mermaidCode }}</div>
        </div>
    @endif
</div>
