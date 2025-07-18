<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Recent Security Alerts
        </x-slot>

        <div class="space-y-2 max-h-96 overflow-y-auto">
            @forelse($alerts as $alert)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $alert['title'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $alert['time_ago'] }}
                        </div>
                        @if($alert['description'])
                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                                {{ Str::limit($alert['description'], 80) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2 ml-3">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                            @if($alert['severity'] === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($alert['severity'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($alert['severity'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @endif">
                            {{ ucfirst($alert['severity']) }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                            @if($alert['status'] === 'new') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($alert['status'] === 'acknowledged') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @elseif($alert['status'] === 'resolved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst($alert['status']) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-gray-600 dark:text-gray-400">No security alerts</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Your system is secure</div>
                    </div>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
