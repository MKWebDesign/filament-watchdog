<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Threat Timeline</h3>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Last 24 hours</span>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h4 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No threats detected</h4>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Your system has been secure for the past 24 hours.</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
