<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Current Threat Level
        </x-slot>

        <div class="text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 mx-auto mb-4
                @if($color === 'red') bg-red-100 dark:bg-red-900
                @elseif($color === 'orange') bg-orange-100 dark:bg-orange-900
                @elseif($color === 'yellow') bg-yellow-100 dark:bg-yellow-900
                @else bg-green-100 dark:bg-green-900
                @endif
                rounded-full">
                @if($color === 'red')
                    <svg class="w-12 h-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.132 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                @elseif($color === 'orange')
                    <svg class="w-12 h-12 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @elseif($color === 'yellow')
                    <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
            </div>
            <div class="text-2xl font-bold
                @if($color === 'red') text-red-600 dark:text-red-400
                @elseif($color === 'orange') text-orange-600 dark:text-orange-400
                @elseif($color === 'yellow') text-yellow-600 dark:text-yellow-400
                @else text-green-600 dark:text-green-400
                @endif
                mb-2">{{ $threat_level }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $description }}</div>

            @if($critical_count > 0 || $high_count > 0 || $malware_count > 0)
                <div class="text-xs text-gray-500 dark:text-gray-500 space-y-1">
                    @if($critical_count > 0)
                        <div>{{ $critical_count }} critical alert(s)</div>
                    @endif
                    @if($high_count > 0)
                        <div>{{ $high_count }} high alert(s)</div>
                    @endif
                    @if($malware_count > 0)
                        <div>{{ $malware_count }} malware detected</div>
                    @endif
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
